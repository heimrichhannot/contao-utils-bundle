<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Template;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use Contao\ThemeModel;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Glob;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\KernelInterface;

class TemplateUtil
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    /**
     * @var FilesystemAdapter
     */
    protected $cache;
    /**
     * @var KernelInterface
     */
    private $kernel;

    public function __construct(ContaoFrameworkInterface $framework, KernelInterface $kernel)
    {
        $this->framework = $framework;
        $this->cache = new FilesystemAdapter('', 0, $kernel->getCacheDir());
        $this->kernel = $kernel;
    }

    /**
     * Return all template files of a particular group as array.
     *
     * @param string $prefix The template name prefix (e.g. "ce_")
     * @param string $format The file extension
     *
     * @return array An array of template names
     *
     * @coversNothing As long as Controller::getTemplateGroup is not testable (ThemeModel…)
     */
    public function getTemplateGroup(string $prefix, string $format = 'html.twig'): array
    {
        $arrTemplates = [];

        $objFilesystem = new Filesystem();
        $files = [];

        try {
            foreach (System::getContainer()->get('contao.resource_finder')->findIn('templates')->name('/'.$prefix.'.*'.$format.'/') as $file) {
                /* @var SplFileInfo $file */
                $strTemplate = $file->getBasename('.'.$format);
                $arrTemplates[$strTemplate]['name'] = $file->getBasename();
                $arrTemplates[$strTemplate]['scopes'][] = rtrim($objFilesystem->makePathRelative($file->getPath(), TL_ROOT), '/');
            }
        } catch (\InvalidArgumentException $e) {
        }

        // Get the default templates
        foreach ($files as $strTemplate) {
            $arrTemplates[$strTemplate]['name'] = basename($strTemplate);
            $arrTemplates[$strTemplate]['scopes'][] = 'root';
        }

        $arrCustomized = $this->findTemplates(TL_ROOT.'/templates/', $prefix, $format);

        // Add the customized templates
        if (\is_array($arrCustomized)) {
            foreach ($arrCustomized as $strFile) {
                $strTemplate = basename($strFile, '.'.$format);
                $arrTemplates[$strTemplate]['name'] = basename($strFile);
                $arrTemplates[$strTemplate]['scopes'][] = $GLOBALS['TL_LANG']['MSC']['global'];
            }
        }

        // Do not look for back end templates in theme folders (see #5379)
        if ('be_' != $prefix && 'mail_' != $prefix) {
            // Try to select the themes (see #5210)
            try {
                /**
                 * @var ThemeModel
                 */
                $adapter = $this->framework->getAdapter(ThemeModel::class);

                $objTheme = $adapter->findAll(['order' => 'name']);
            } catch (\Exception $e) {
                $objTheme = null;
            }

            // Add the theme templates
            if (null !== $objTheme) {
                while ($objTheme->next()) {
                    if ('' != $objTheme->templates) {
                        $arrThemeTemplates = $this->findTemplates(TL_ROOT.'/'.$objTheme->templates.'/', $prefix, $format);

                        if (\is_array($arrThemeTemplates)) {
                            foreach ($arrThemeTemplates as $strFile) {
                                $strTemplate = basename($strFile, '.'.$format);
                                $arrTemplates[$strTemplate]['name'] = basename($strFile);
                                $arrTemplates[$strTemplate]['scopes'][] = $objTheme->name;
                            }
                        }
                    }
                }
            }
        }

        // Show the template sources (see #6875)
        foreach ($arrTemplates as $k => $v) {
            $scope = array_filter($v['scopes'], function ($a) {
                return 'root' != $a;
            });

            if (empty($v)) {
                $arrTemplates[$k] = $v['name'];
            } else {
                $arrTemplates[$k] = $v['name'].' ('.implode(', ', $scope).')';
            }
        }

        // Sort the template names
        ksort($arrTemplates);

        return $arrTemplates;
    }

    /**
     * Find a particular template file and return its path.
     *
     * @param string $name   The name of the template
     * @param string $format The file extension
     *
     * @throws \InvalidArgumentException           If $strFormat is unknown
     * @throws \RuntimeException                   If the template group folder is insecure
     * @throws \Psr\Cache\InvalidArgumentException
     *
     * @return string The path to the template file
     */
    public function getTemplate(string $name, string $format = 'html.twig'): string
    {
        $cache = $this->cache->getItem('templates');
        $templates = $cache->get();

        if ($cache->isHit() && isset($templates[$name]) && !empty($templates[$name])) {
            return $templates[$name];
        }

        // allow twig templates
        $GLOBALS['TL_CONFIG']['templateFiles'] .= ',html.twig';

        try {
            $path = Controller::getTemplate($name, $format);

            if (file_exists($path)) {
                $templatePath = $path;
            } else {
                $templatePath = $this->getBundleTemplate($name, $format);
            }
        } catch (\Exception $e) {
            $templatePath = $this->getBundleTemplate($name, $format);
        }

        $templates[$name] = $templatePath;
        $cache->set($templates);
        $this->cache->save($cache);

        return $templatePath;
    }

    /**
     * Return the files matching a GLOB pattern.
     *
     * @param string $path
     * @param string $pattern
     * @param string $format
     *
     * @return array
     */
    public function findTemplates(string $path, string $pattern = null, string $format = 'html.twig')
    {
        // Use glob() if possible
        if (false === strpos($path, '/**/') && (\defined('GLOB_BRACE') || false === strpos($path, '{'))) {
            $templates = glob(rtrim($path, '/').'/*.{'.$format.'}', \defined('GLOB_BRACE') ? GLOB_BRACE : 0);

            return null === $pattern ? $templates : preg_grep('$'.$pattern.'$', $templates);
        }

        $pattern = rtrim($path, '/').(null === $pattern ? '' : $pattern).'/*.{'.$format.'}';

        $finder = new Finder();
        $regex = Glob::toRegex($pattern);

        // All files in the given template folder
        $filesIterator = $finder->files()->followLinks()->sortByName()->in(\dirname($pattern));

        // Match the actual regex and filter the files
        $filesIterator = $filesIterator->filter(function (\SplFileInfo $info) use ($regex) {
            $path = $info->getPathname();

            return preg_match($regex, $path) && $info->isFile();
        });

        $files = iterator_to_array($filesIterator);

        return array_keys($files);
    }

    /**
     * remove TEMPLATE START/END comment from template if in debug mode.
     *
     * @param $section
     *
     * @return mixed
     */
    public function removeTemplateComment($template)
    {
        $template = Controller::replaceInsertTags($template);

        if (!empty($template)) {
            $template = preg_replace('/<!-- TEMPLATE (.*?)-->/', '', $template);
            $template = trim(preg_replace('/\r?\n|\r/', '', $template));
        }

        return $template;
    }

    /**
     * Return true, if the template part is empty.
     * Template comments from debug and white spaces are treated as empty.
     *
     * @param string $template
     *
     * @return bool
     */
    public function isTemplatePartEmpty($template = null)
    {
        if (!\is_string($template)) {
            return false;
        }

        return empty($this->removeTemplateComment($template));
    }

    /**
     * Find a particular template file within all bundles and return its path.
     *
     * @param string $name   The name of the template
     * @param string $format The file extension
     *
     * @throws \InvalidArgumentException If $strFormat is unknown
     * @throws \RuntimeException         If the template group folder is insecure
     *
     * @return string The path to the template file
     */
    protected function getBundleTemplate(string $name, string $format = 'html.twig'): string
    {
        $templatePath = $name;

        $bundles = $this->kernel->getBundles();
        // if file from Controller::getTemplate() does not exist, search template in bundle views directory and return twig bundle path
        if (\is_array($bundles) && 'html.twig' === $format) {
            $pattern = $name.'.'.$format;

            foreach ($bundles as $key => $value) {
                $path = $this->kernel->locateResource("@$key");
                $finder = new Finder();
                $finder->in($path);
                $finder->files()->name($pattern);
                $twigKey = preg_replace('/Bundle$/', '', $key);

                foreach ($finder as $val) {
                    $explodurl = explode('Resources'.\DIRECTORY_SEPARATOR.'views'.\DIRECTORY_SEPARATOR, $val->getRelativePathname());
                    $string = end($explodurl);
                    $templatePath = "@$twigKey/$string";

                    break 2;
                }
            }
        }

        return $templatePath;
    }
}

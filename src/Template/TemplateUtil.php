<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Template;

use Contao\Controller;
use Contao\PageModel;
use Contao\ThemeModel;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Event\RenderTwigTemplateEvent;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Glob;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\KernelInterface;

class TemplateUtil
{
    /**
     * @var FilesystemAdapter
     */
    protected $cache;

    /**
     * Known files.
     *
     * @var array
     */
    protected static $twigFiles = [];
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var ContainerUtil
     */
    protected $containerUtil;
    /**
     * @var KernelInterface
     */
    private $kernel;

    public function __construct(ContainerInterface $container)
    {
        $this->kernel = $container->get('kernel');
        $this->container = $container;
        $this->containerUtil = $container->get('huh.utils.container');
    }

    /**
     * Get a list of all available templates.
     */
    public function getAllTemplates()
    {
        $strCacheDir = $this->container->getParameter('kernel.cache_dir');

        // Try to load from cache
        if (file_exists($strCacheDir.'/contao/config/twig-templates.php')) {
            self::$twigFiles = include $strCacheDir.'/contao/config/twig-templates.php';

            return self::$twigFiles;
        }

        $bundles = $this->kernel->getBundles();

        if (\is_array($bundles)) {
            foreach (array_reverse($bundles) as $key => $value) {
                $path = $this->kernel->locateResource("@$key");

                $dir = rtrim($path, '/').'/Resources/views';

                if (!is_dir($dir)) {
                    continue;
                }
                $finder = new Finder();
                $twigKey = preg_replace('/Bundle$/', '', $key);

                foreach ($finder->in($dir)->files()->name('*.twig') as $file) {
                    /** @var SplFileInfo $file */
                    $name = $file->getBasename();
                    $legacyName = false !== strpos($name, 'html.twig') ? $file->getBasename('.html.twig') : $name;

                    if (isset(self::$twigFiles[$name])) {
                        continue;
                    }

                    self::$twigFiles[$name] = "@$twigKey/".$file->getRelativePathname();

                    if ($legacyName !== $name) {
                        self::$twigFiles[$legacyName] = self::$twigFiles[$name];
                    }
                }
            }
        }

        foreach ($this->container->get('contao.resource_finder')->findIn('templates')->name('*.twig') as $file) {
            $name = $file->getBasename();
            $legacyName = false !== strpos($name, 'html.twig') ? $file->getBasename('.html.twig') : $name;

            /* @var SplFileInfo $file */
            self::$twigFiles[$name] = $file->getRealPath();

            if ($legacyName !== $name) {
                self::$twigFiles[$legacyName] = self::$twigFiles[$name];
            }
        }

        // add root templates
        $rootTemplates = $this->findTemplates($this->containerUtil->getProjectDir().'/templates/');

        if (\is_array($rootTemplates)) {
            foreach ($rootTemplates as $file) {
                $name = basename($file);
                $legacyName = false !== strpos($name, 'html.twig') ? basename($file, '.html.twig') : $name;

                self::$twigFiles[$name] = $file;

                if ($legacyName !== $name) {
                    self::$twigFiles[$legacyName] = self::$twigFiles[$name];
                }
            }
        }

        return self::$twigFiles;
    }

    /**
     * Return all template files of a particular group as array.
     *
     * @param string $prefix The template name prefix (e.g. "ce_")
     * @param string $format The file extension
     *
     * @return array An array of template names (html.twig templates without file extension, others with file extension)
     *
     * @coversNothing As long as Controller::getTemplateGroup is not testable (ThemeModel…)
     */
    public function getTemplateGroup(string $prefix, string $format = 'html.twig'): array
    {
        $arrTemplates = [];

        $objFilesystem = new Filesystem();
        $files = [];

        try {
            $bundles = $this->kernel->getBundles();
            // if file from Controller::getTemplate() does not exist, search template in bundle views directory and return twig bundle path
            if (\is_array($bundles) && '.twig' === substr($format, -5)) {
                foreach (array_reverse($bundles) as $key => $value) {
                    $path = $this->kernel->locateResource("@$key");
                    $dir = rtrim($path, '/').'/Resources/views';

                    if (!is_dir($dir)) {
                        continue;
                    }
                    $finder = new Finder();
                    $finder->in($dir);
                    $finder->files()->name('/'.$prefix.'.*'.$format.'/');

                    /* @var SplFileInfo $file */
                    foreach ($finder as $file) {
                        $strTemplate = 'html.twig' === $format ? $file->getBasename('.'.$format) : $file->getFilename(); // Backward compability for html.twig templates
                        $arrTemplates[$strTemplate]['name'] = $file->getBasename();
                        $arrTemplates[$strTemplate]['scopes'][] = rtrim($objFilesystem->makePathRelative($file->getPath(), $this->containerUtil->getProjectDir()), '/');
                    }
                }
            }

            foreach ($this->container->get('contao.resource_finder')->findIn('templates')->name('/'.$prefix.'.*'.$format.'/') as $file) {
                /* @var SplFileInfo $file */
                $strTemplate = 'html.twig' === $format ? $file->getBasename('.'.$format) : $file->getFilename(); // Backward compability for html.twig templates
                $arrTemplates[$strTemplate]['name'] = $file->getBasename();
                $arrTemplates[$strTemplate]['scopes'][] = rtrim($objFilesystem->makePathRelative($file->getPath(), $this->containerUtil->getProjectDir()), '/');
            }
        } catch (\InvalidArgumentException $e) {
        }

        // Get the default templates
        foreach ($files as $strTemplate) {
            $arrTemplates[$strTemplate]['name'] = basename($strTemplate);
            $arrTemplates[$strTemplate]['scopes'][] = 'root';
        }

        $arrCustomized = $this->findTemplates($this->containerUtil->getProjectDir().'/templates/', $prefix, $format);

        // Add the customized templates
        if (\is_array($arrCustomized)) {
            foreach ($arrCustomized as $strFile) {
                $strTemplate = 'html.twig' === $format ? basename($strFile, '.'.$format) : basename($strFile); // Backward compability for html.twig templates
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
                $adapter = $this->container->get('contao.framework')->getAdapter(ThemeModel::class);

                $objTheme = $adapter->findAll(['order' => 'name']);
            } catch (\Exception $e) {
                $objTheme = null;
            }

            // Add the theme templates
            if (null !== $objTheme) {
                while ($objTheme->next()) {
                    if ('' != $objTheme->templates) {
                        $arrThemeTemplates = $this->findTemplates($this->containerUtil->getProjectDir().'/'.$objTheme->templates.'/', $prefix, $format);

                        if (\is_array($arrThemeTemplates)) {
                            foreach ($arrThemeTemplates as $strFile) {
                                $strTemplate = 'html.twig' === $format ? basename($strFile, '.'.$format) : basename($strFile); // Backward compability for html.twig templates
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
            $scope = array_filter(
                $v['scopes'],
                function ($a) {
                    return 'root' != $a;
                }
            );

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
     * @throws \InvalidArgumentException If $strFormat is unknown
     * @throws \RuntimeException         If the template group folder is insecure
     * @throws \Twig_Error_Loader
     *
     * @return string The path to the template file
     */
    public function getTemplate(string $name, string $format = 'twig'): string
    {
        // Check for a theme folder
        if ($this->containerUtil->isFrontend()) {
            /* @var PageModel $objPage */
            global $objPage;

            if ('' != $objPage->templateGroup) {
                if (\Validator::isInsecurePath($objPage->templateGroup)) {
                    throw new \RuntimeException('Invalid path '.$objPage->templateGroup);
                }

                $templates = $this->findTemplates($this->containerUtil->getProjectDir().'/'.$objPage->templateGroup, $name, $format);

                if (!empty($templates)) {
                    return reset($templates);
                }
            }
        }

        if (!isset(self::$twigFiles[$name])) {
            throw new \Twig_Error_Loader(sprintf('Unable to find template "%s".', $name));
        }

        return self::$twigFiles[$name];
    }

    /**
     * Return the files matching a GLOB pattern.
     *
     * @param string $pattern
     *
     * @return array
     */
    public function findTemplates(string $path, string $pattern = null, string $format = 'twig')
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
        $filesIterator = $filesIterator->filter(
            function (\SplFileInfo $info) use ($regex) {
                $path = $info->getPathname();

                return preg_match($regex, $path) && $info->isFile();
            }
        );

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
     * Datatypes other than string (typical null) are also treated as empty.
     *
     * @param string $template
     *
     * @return bool
     */
    public function isTemplatePartEmpty($template = null)
    {
        if (!\is_string($template)) {
            return true;
        }

        return empty($this->removeTemplateComment($template));
    }

    public function getPageAliasAsCssClass()
    {
        global $objPage;

        return str_replace(['/', 'ä', 'ö', 'ü'], ['_', 'ae', 'oe', 'ue'], $objPage->alias);
    }

    /**
     * Renders a twig template with data.
     *
     * @param string $name    The twig template name
     * @param array  $context The twig template context data
     *
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @return string
     */
    public function renderTwigTemplate(string $name, array $context = [])
    {
        $event = $this->container->get('event_dispatcher')->dispatch(
            RenderTwigTemplateEvent::NAME,
            new RenderTwigTemplateEvent(
                $name, $context
            )
        );

        $buffer = $this->container->get('twig')->render(
            $this->getTemplate($event->getTemplate()),
            $event->getContext()
        );

        return $buffer;
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
    public function getBundleTemplate(string $name, string $format = 'html.twig'): string
    {
        $templatePath = $name;

        $bundles = $this->kernel->getBundles();
        // if file from Controller::getTemplate() does not exist, search template in bundle views directory and return twig bundle path
        if (\is_array($bundles) && '.twig' === substr($format, -5)) {
            $pattern = $name.'.'.$format;

            foreach (array_reverse($bundles) as $key => $value) {
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

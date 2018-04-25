<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Template;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\ThemeModel;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Glob;
use Symfony\Component\Finder\SplFileInfo;

class TemplateUtil
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
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
            foreach (\System::getContainer()->get('contao.resource_finder')->findIn('templates')->name('*'.$format) as $file) {
                /* @var SplFileInfo $file */
                $files[$file->getBasename($format)] = rtrim($objFilesystem->makePathRelative($file->getPath(), TL_ROOT), '/');
            }
        } catch (\InvalidArgumentException $e) {
        }

        $prefixedFiles = array_values(preg_grep('/^'.rtrim($prefix, '_').'($|_)/', array_keys($files)));

        // Get the default templates
        foreach ($prefixedFiles as $strTemplate) {
            $arrTemplates[$strTemplate]['name'] = basename($strTemplate);
            $arrTemplates[$strTemplate]['scopes'][] = 'root';
        }

        $strBrace = '{'.$format.'}';
        $arrCustomized = $this->braceGlob(TL_ROOT.'/templates/'.$prefix.'*.'.$strBrace);

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
                        $arrThemeTemplates = $this->braceGlob(TL_ROOT.'/'.$objTheme->templates.'/'.$prefix.'*.'.$strBrace);

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
     * @throws \InvalidArgumentException If $strFormat is unknown
     * @throws \RuntimeException         If the template group folder is insecure
     *
     * @return string The path to the template file
     */
    public function getTemplate(string $name, string $format = 'html.twig'): string
    {
        // allow twig templates
        $GLOBALS['TL_CONFIG']['templateFiles'] .= ',html.twig';

        return Controller::getTemplate($name, $format);
    }

    /**
     * Return the files matching a GLOB pattern.
     *
     * @param string $pattern
     *
     * @return array
     */
    public function braceGlob($pattern)
    {
        // Use glob() if possible
        if (false === strpos($pattern, '/**/') && (\defined('GLOB_BRACE') || false === strpos($pattern, '{'))) {
            return glob($pattern, \defined('GLOB_BRACE') ? GLOB_BRACE : 0);
        }

        $finder = new Finder();
        $regex = Glob::toRegex($pattern);

        // All files in the given template folder
        $filesIterator = $finder
            ->files()
            ->followLinks()
            ->sortByName()
            ->in(\dirname($pattern));

        // Match the actual regex and filter the files
        $filesIterator = $filesIterator->filter(function (\SplFileInfo $info) use ($regex) {
            $path = $info->getPathname();

            return preg_match($regex, $path) && $info->isFile();
        });

        $files = iterator_to_array($filesIterator);

        return array_keys($files);
    }
}

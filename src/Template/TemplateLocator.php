<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Template;

use Contao\CoreBundle\Config\ResourceFinder;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Glob;
use Symfony\Component\HttpKernel\KernelInterface;

class TemplateLocator
{
    /**
     * Known files.
     *
     * @var array
     */
    protected $twigFiles = [];
    /**
     * @var string
     */
    private $cacheDir;
    /**
     * @var KernelInterface
     */
    private $kernel;
    /**
     * @var ResourceFinder
     */
    private $resourceFinder;
    /**
     * @var string
     */
    private $projectDir;

    /**
     * TemplateLocator constructor.
     */
    public function __construct(string $cacheDir, KernelInterface $kernel, ResourceFinder $resourceFinder, string $projectDir)
    {
        $this->cacheDir = $cacheDir;
        $this->kernel = $kernel;
        $this->resourceFinder = $resourceFinder;
        $this->projectDir = $projectDir;
    }

    /**
     * Returns a list of all available template.
     * This includes bundles and project template folder.
     *
     * @return array
     */
    public function getAllTemplates()
    {
        // Try to load from cache
        if (file_exists($this->cacheDir.'/contao/config/twig-templates.php')) {
            $this->twigFiles = include $this->cacheDir.'/contao/config/twig-templates.php';

            return $this->twigFiles;
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

                    if (isset($this->twigFiles[$name])) {
                        continue;
                    }

                    $this->twigFiles[$name] = "@$twigKey/".$file->getRelativePathname();

                    if ($legacyName !== $name) {
                        $this->twigFiles[$legacyName] = $this->twigFiles[$name];
                    }
                }
            }
        }

        foreach ($this->resourceFinder->findIn('templates')->name('*.twig') as $file) {
            $name = $file->getBasename();
            $legacyName = false !== strpos($name, 'html.twig') ? $file->getBasename('.html.twig') : $name;

            /* @var SplFileInfo $file */
            $this->twigFiles[$name] = $file->getRealPath();

            if ($legacyName !== $name) {
                $this->twigFiles[$legacyName] = $this->twigFiles[$name];
            }
        }

        // add root templates
        $rootTemplates = $this->findTemplates($this->projectDir.'/templates/');

        if (\is_array($rootTemplates)) {
            foreach ($rootTemplates as $file) {
                $name = basename($file);
                $legacyName = false !== strpos($name, 'html.twig') ? basename($file, '.html.twig') : $name;

                $this->twigFiles[$name] = $file;

                if ($legacyName !== $name) {
                    $this->twigFiles[$legacyName] = $this->twigFiles[$name];
                }
            }
        }

        return $this->twigFiles;
    }

    /**
     * Return the files matching a GLOB pattern.
     *
     * @param string $pattern
     *
     * @return array
     */
    public static function findTemplates(string $path, string $pattern = null, string $format = 'twig')
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
}

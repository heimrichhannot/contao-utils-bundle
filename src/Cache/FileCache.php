<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Cache;

use Contao\File;
use Contao\StringUtil;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class FileCache.
 *
 * The cache will be created in the cache folder defined by the config (default the huh.utils.filecache.folder parameter)
 * Within the cache folder you can specify a namespace serving as subfolder.
 *
 *
 * @example /src/Pdf/PdfPreview.php
 */
class FileCache
{
    /**
     * The folder where the images should be cached.
     *
     * @var string
     */
    protected $cacheFolder;

    /**
     * The subfolder within the cache folder.
     *
     * @var string
     */
    protected $namespace;

    /**
     * The complete path to the current cache folder including namespace.
     *
     * @var string
     */
    protected $cacheFolderWithNamespace;

    /**
     * @var FileUtil
     */
    private $fileUtil;
    /**
     * @var string
     */
    private $webDir;

    public function __construct(string $cacheFolder, string $webDir, FileUtil $fileUtil, string $namespace = '')
    {
        $this->cacheFolder = $cacheFolder;
        $this->namespace = $namespace;
        $this->fileUtil = $fileUtil;
        $this->webDir = $webDir.'/..';
        $this->generatePath();
    }

    /**
     * Checks if a cached file already exist.
     *
     * @param string $identifier    The identifier
     * @param string $fileExtension
     *
     * @return bool
     */
    public function exist(string $identifier, string $fileExtension)
    {
        $fileName = $this->getCacheFileName($identifier);
        $file = new File($this->cacheFolderWithNamespace.'/'.$fileName.'.'.$fileExtension);

        if ($file->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Get the file path for the given identifier.
     *
     * @param string   $identifier
     * @param string   $fileExtension
     * @param callable $saveCallback  A callback handles the file save functionality. Get filepath, filename and the identifier as parameter. Expects a boolean return value.
     *
     * @return bool|string
     */
    public function get(string $identifier, string $fileExtension, callable $saveCallback = null)
    {
        $fileName = $this->getCacheFileName($identifier).'.'.$fileExtension;
        $file = new File($this->cacheFolderWithNamespace.'/'.$fileName);

        if (!$file->exists()) {
            if (null !== $saveCallback) {
                if ($saveCallback($identifier, $this->cacheFolderWithNamespace, $fileName)) {
                    return $this->cacheFolderWithNamespace.'/'.$fileName;
                }
            }

            return false;
        }

        return $file->path;
    }

    /**
     * Generate a file name for cache.
     *
     * If a identifier is given, you get the resulting file name.
     * If no identifier is given, if will return a unique file name without extension.
     *
     * @param string $identifier    An identifier for the cache. For example be the source file name or path. If empty, a unique filename will be generated.
     * @param string $prefix        Adds a prefix to the generated name. Only if $identifier is empty.
     * @param bool   $more_entropy  A longer name for the unique filename. Only if $identifier is empty. Default
     * @param string $fileExtension optional: If set, the file extension will be appended to the generated file name
     *
     * @return string a unique filename for caching
     */
    public function generateCacheName(string $identifier = '', string $prefix = '', bool $more_entropy = true, string $fileExtension = '')
    {
        if (empty($identifier)) {
            $fileName = uniqid($prefix, $more_entropy);
        } else {
            $fileName = $this->getCacheFileName($identifier);
        }

        if (!empty($fileExtension)) {
            $fileName .= '.'.$fileExtension;
        }

        return $fileName;
    }

    /**
     * Get the cache file name by the given identifier.
     *
     * @param $identifier
     *
     * @return string
     */
    public function getCacheFileName($identifier)
    {
        return StringUtil::generateAlias($identifier);
    }

    /**
     * Same as generateCacheName, but returns complete path to cache.
     *
     * If no filename is given, you need to add the file extension by yourself!
     *
     * @param string $filename     The filename of the file that should be cached. If empty, a unique filename will be generated.
     * @param string $prefix       Adds a prefix to the generated name. Only if $filename is empty.
     * @param bool   $more_entropy A longer name for the unique filename. Only if filename is empty. Default
     *
     * @return string the path including the filename to save the file to the cache
     */
    public function getCacheFilePath(string $filename = '', string $prefix = '', bool $more_entropy = true)
    {
        return $this->cacheFolderWithNamespace.'/'.$this->generateCacheName($filename, $prefix, $more_entropy);
    }

    /**
     * Returns the absolute path to the cache folder.
     *
     * @return string
     */
    public function getAbsoluteCachePath()
    {
        return $this->webDir.'/'.$this->cacheFolderWithNamespace;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace(string $namespace)
    {
        $this->namespace = trim($namespace, ' /');
        $this->generatePath();
    }

    /**
     * The cache folder (without namespace).
     *
     * @return string
     */
    public function getCacheFolder(): string
    {
        return $this->cacheFolder;
    }

    /**
     * Set cache folder (without namespace).
     *
     * @param string $cacheFolder
     */
    public function setCacheFolder(string $cacheFolder)
    {
        $this->cacheFolder = $cacheFolder;
        $this->generatePath();
    }

    /**
     * Get the cache folder (including namespace).
     *
     * @return string
     */
    public function getCacheFolderWithNamespace(): string
    {
        return $this->cacheFolderWithNamespace;
    }

    /**
     * Recreates the path to the current cache folder.
     */
    protected function generatePath()
    {
        $filesystem = new Filesystem();
        $path = $this->cacheFolder;

        if (!empty($this->namespace)) {
            $path .= '/'.$this->namespace;
        }

        if (!$filesystem->exists($this->webDir.'/'.$path)) {
            $filesystem->mkdir($this->webDir.'/'.$path);
        }
        $this->cacheFolderWithNamespace = $path;
    }
}

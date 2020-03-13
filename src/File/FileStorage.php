<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\File;

use Ausi\SlugGenerator\SlugGenerator;
use Symfony\Component\Filesystem\Filesystem;

class FileStorage
{
    /**
     * @var string
     */
    private $relativeStoragePath;
    /**
     * @var SlugGenerator
     */
    private $generator;
    /**
     * @var string
     */
    private $defaultFileExtension;
    /**
     * @var string
     */
    private $rootPath;

    /**
     * FileCache constructor.
     *
     * @param string $rootPath             The project root path
     * @param string $storagePath          The storage path relative to the project root path
     * @param string $defaultFileExtension The default file extension of the files to store
     */
    public function __construct(string $rootPath, string $storagePath, string $defaultFileExtension = '')
    {
        $this->rootPath = rtrim($rootPath, \DIRECTORY_SEPARATOR);
        $this->relativeStoragePath = trim($storagePath, \DIRECTORY_SEPARATOR);
        $this->generator = new SlugGenerator();
        $this->filesystem = new Filesystem();
        $this->defaultFileExtension = $defaultFileExtension;
    }

    /**
     * Get the storage path for given identifier. Caution: Identifier will be normalized!
     *
     * Options:
     * - fileExtension: (string) Override the default file extension.
     *
     * @param string      $key     the key for the item in store
     * @param string|null $default Default value to return if key does not exist
     * @param array       $options Additional options
     *
     * @throws \Exception
     *
     * @return string|null return the path of the item from the storage, or $default if not found
     */
    public function get(string $key, ?string $default = null, array $options = []): ?string
    {
        $filename = $this->createFilename($key, $options);
        $absoluteFilePath = $this->createAbsoluteFilePath($filename);

        if (!$this->filesystem->exists($absoluteFilePath)) {
            return $default;
        }

        return $this->createRelativeFilePath($filename);
    }

    /**
     * Persist a file in the file storage, uniquely references by an identifier. Caution: identifier will be normalized!
     *
     * Value:
     * - callable: Gets a FileStorageCallback object as parameter and must return bool (true on success, false otherwise).
     * - string: Will be directly written to the file.
     *
     * Options:
     * - fileExtension: (string) Override the default file extension.
     *
     * @param string          $identifier the key of the item to store
     * @param callable|string $value      The value of the item to store. Must be a callable or string.
     *
     * @throws \InvalidArgumentException Is thrown when value is neither callable or string
     * @throws \UnexpectedValueException is thrown if callback not returning a bool
     *
     * @return string return the path of the item from the storage
     */
    public function set(string $identifier, $value, array $options = []): string
    {
        $filename = $this->createFilename($identifier, $options);
        $relativeFilePath = $this->createRelativeFilePath($filename);
        $absoluteFilePath = $this->createAbsoluteFilePath($filename);

        if (\is_callable($value)) {
            $fileStorageCallback = new FileStorageCallback($identifier, $filename, $relativeFilePath, $absoluteFilePath, $this->rootPath, $this->relativeStoragePath);
            $result = $value($fileStorageCallback);

            if (!\is_bool($result)) {
                throw new \UnexpectedValueException('Invalid callback return type. Must be bool, was '.\gettype($result).'.');
            }

            if (!$result) {
                throw new \RuntimeException('File could not be created. Callback returned false.');
            }
        } else {
            if (!\is_string($value)) {
                throw new \InvalidArgumentException('Invalid value, must be of type string or callable, was '.\gettype($value).'.');
            }
            $this->filesystem->dumpFile($absoluteFilePath, $value);
        }

        return $relativeFilePath;
    }

    /**
     * Normalize the key.
     *
     * @return string
     */
    protected function normalizeKey(string $key)
    {
        return $this->generator->generate($key, ['validChars' => 'a-z0-9']);
    }

    /**
     * Create the filename out of key and file extension.
     */
    protected function createFilename(string $key, array $options): string
    {
        $filename = $this->normalizeKey($key);
        $fileExtension = null;

        if (!empty($this->defaultFileExtension)) {
            $fileExtension = $this->defaultFileExtension;
        }

        if (isset($options['fileExtension']) && \is_string($options['fileExtension'])) {
            $fileExtension = $options['fileExtension'];
        }

        if ($fileExtension) {
            $filename .= '.'.$fileExtension;
        }

        return $filename;
    }

    /**
     * Return the absoulte path to the file.
     */
    protected function createAbsoluteFilePath(string $filename): string
    {
        $storagePath = $this->rootPath.\DIRECTORY_SEPARATOR.$this->relativeStoragePath.\DIRECTORY_SEPARATOR.$filename;

        return $storagePath;
    }

    /**
     * Return the relative path to the file.
     */
    protected function createRelativeFilePath(string $filename): string
    {
        $storagePath = $this->relativeStoragePath.\DIRECTORY_SEPARATOR.$filename;

        return $storagePath;
    }
}

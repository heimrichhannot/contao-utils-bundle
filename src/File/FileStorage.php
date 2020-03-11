<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\UtilsBundle\File;


use Ausi\SlugGenerator\SlugGenerator;
use Contao\File;

class FileStorage
{
    /**
     * @var string
     */
    private $storagePath;
    /**
     * @var SlugGenerator
     */
    private $generator;
    /**
     * @var string
     */
    private $defaultFileExtension;

    /**
     * FileCache constructor.
     */
    public function __construct(string $storagePath, string $defaultFileExtension = '')
    {
        $this->storagePath = $storagePath;
        $this->generator   = new SlugGenerator();
        $this->defaultFileExtension = $defaultFileExtension;
    }

    /**
     * Get the storage path for given identifier. Caution: Identifier will be normalized!
     *
     * Options:
     * - fileExtension: (string) Override the default file extension.
     *
     * @param string $key The key for the item in store.
     * @param string|null $default Default value to return if key does not exist
     * @param array $options Additional options
     * @return string|null Return the path of the item from the storage, or $default if not found.
     * @throws \Exception
     */
    public function get(string $key, ?string $default = null, array $options = []): ?string
    {
        $filename    = $this->createFilename($key, $options);
        $storagePath = $this->storagePath.DIRECTORY_SEPARATOR.$filename;

        $file = new File($storagePath);

        if (!$file->exists()) {
            return $default;
        }

        return $file->path;
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
     * @param string $identifier The key of the item to store.
     * @param callable|string $value The value of the item to store. Must be a callable or string.
     * @param array $options
     * @return bool
     * @throws \InvalidArgumentException Is thrown when value is neither callable or string
     * @throws \UnexpectedValueException Is thrown if callback not returning a bool.
     * @throws \RuntimeException Is thrown when there is an io error when opening or writing a file.
     */
    public function set(string $identifier, $value, array $options = []): string
    {
        $filename = $this->createFilename($identifier, $options);
        $storagePath = $this->storagePath.DIRECTORY_SEPARATOR.$filename;

        try
        {
            $file = new File($storagePath);
        } catch (\Exception $e)
        {
            throw new \RuntimeException("Error while creating store file object: ".$e->getMessage());
        }

        if (is_callable($value)) {
            $fileStorageCallback = new FileStorageCallback($file, $identifier, $storagePath, $filename);
            $result = $value($fileStorageCallback);
            if (!is_bool($result)) {
                throw new \UnexpectedValueException("Invalid callback return type. Must be bool, was ".gettype($result).".");
            }
            return $storagePath;
        }
        if (!is_string($value)) {
            throw new \InvalidArgumentException("Invalid value, must be of type string or callable, was ".gettype($value).".");
        }

        if (!$file->write($value)) {
            throw new \RuntimeException("Could not write file.");
        }
        return $storagePath;
    }

    /**
     * Normalize the key
     *
     * @param string $key
     * @return string
     */
    protected function normalizeKey(string $key)
    {
        return $this->generator->generate($key, ['validChars' => 'a-z0-9']);
    }

    /**
     * Create the filename out of key and file extension
     *
     * @param string $key
     * @param array $options
     * @return string
     */
    protected function createFilename(string $key, array $options): string
    {
        $filename = $this->normalizeKey($key);
        $fileExtension = null;
        if (!empty($this->defaultFileExtension))
        {
            $fileExtension = $this->defaultFileExtension;
        }
        if (isset($options['fileExtension']) && is_string($options['fileExtension']))
        {
            $fileExtension = $options['fileExtension'];
        }
        if ($fileExtension) {
            $filename .= '.' . $fileExtension;
        }
        return $filename;
    }
}
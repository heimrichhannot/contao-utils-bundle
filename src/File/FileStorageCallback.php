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


use Contao\File;

class FileStorageCallback
{
    /**
     * @var File
     */
    private $file;
    /**
     * @var string
     */
    private $identifier;
    /**
     * @var string
     */
    private $storagePath;
    /**
     * @var string
     */
    private $filename;

    /**
     * FileStorageCallback constructor.
     */
    public function __construct(File $file, string $identifier, string $storagePath, string $filename)
    {

        $this->file = $file;
        $this->identifier = $identifier;
        $this->storagePath = $storagePath;
        $this->filename = $filename;
    }

    /**
     * @return File
     */
    public function getFile(): File
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getStoragePath(): string
    {
        return $this->storagePath;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }
}
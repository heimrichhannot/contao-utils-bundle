<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\File;

class FileStorageCallback
{
    /**
     * @var string
     */
    private $identifier;
    /**
     * @var string
     */
    private $filename;
    /**
     * @var string
     */
    private $relativeFilePath;
    /**
     * @var string
     */
    private $absoluteFilePath;
    /**
     * @var string
     */
    private $rootPath;
    /**
     * @var string
     */
    private $relativeStoragePath;

    /**
     * FileStorageCallback constructor.
     */
    public function __construct(string $identifier, string $filename, string $relativeFilePath, string $absoluteFilePath, string $rootPath, string $relativeStoragePath)
    {
        $this->identifier = $identifier;
        $this->filename = $filename;
        $this->relativeFilePath = $relativeFilePath;
        $this->absoluteFilePath = $absoluteFilePath;
        $this->rootPath = $rootPath;
        $this->relativeStoragePath = $relativeStoragePath;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getRelativeFilePath(): string
    {
        return $this->relativeFilePath;
    }

    public function getAbsoluteFilePath(): string
    {
        return $this->absoluteFilePath;
    }

    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    public function getRelativeStoragePath(): string
    {
        return $this->relativeStoragePath;
    }
}

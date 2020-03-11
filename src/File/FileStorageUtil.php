<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\File;

class FileStorageUtil
{
    /**
     * @var string
     */
    private $projectDir;

    /**
     * FileStorageUtil constructor.
     */
    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * Returns a new FileStorage instance.
     *
     * @return FileStorage
     */
    public function createFileStorage(string $storagePath, string $fileExtension = '')
    {
        return new FileStorage($this->projectDir, $storagePath, $fileExtension);
    }
}

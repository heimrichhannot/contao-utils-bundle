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
     * See PdfPreview for example usage.
     *
     * @param string $storagePath   The path where to store the files relative to the project dir
     * @param string $fileExtension The default file extension of the stored files. E.g. jpg, txt, ...
     *
     * @return FileStorage
     */
    public function createFileStorage(string $storagePath, string $fileExtension = '')
    {
        return new FileStorage($this->projectDir, $storagePath, $fileExtension);
    }
}

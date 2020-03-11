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

class FileStorageUtil
{
    /**
     * @var SlugGenerator
     */
    private $generator;

    public function createFileStorage(string $storagePath, string $fileExtension = '')
    {
        return new FileStorage($storagePath, $fileExtension);
    }



    
}
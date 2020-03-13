<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\File;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\File\FileStorage;
use HeimrichHannot\UtilsBundle\File\FileStorageUtil;
use Symfony\Component\Filesystem\Filesystem;

class FileStorageUtilTest extends ContaoTestCase
{
    public function testCreateFileStorage()
    {
        $instance = new FileStorageUtil($this->getTempDir());
        $fileStorage = $instance->createFileStorage('filestorageutil', 'jpg');
        $this->assertInstanceOf(FileStorage::class, $fileStorage);

        $fileStorage->set('testimage', 'FF DD');
        $filesystem = new Filesystem();
        $filesystem->exists($this->getTempDir().'/filestorageutil/testimage.jpg');
    }
}

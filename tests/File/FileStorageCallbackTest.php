<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\File;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\File\FileStorageCallback;

class FileStorageCallbackTest extends ContaoTestCase
{
    public function testGetter()
    {
        $instance = new FileStorageCallback('Hello', 'hello.jpg', 'files/storage/hello.jpg', '/var/html/files/storage/hello.jpg', '/var/html', 'files/storage');

        $this->assertSame('Hello', $instance->getIdentifier());
        $this->assertSame('hello.jpg', $instance->getFilename());
        $this->assertSame('files/storage/hello.jpg', $instance->getRelativeFilePath());
        $this->assertSame('/var/html/files/storage/hello.jpg', $instance->getAbsoluteFilePath());
        $this->assertSame('/var/html', $instance->getRootPath());
        $this->assertSame('files/storage', $instance->getRelativeStoragePath());
    }
}

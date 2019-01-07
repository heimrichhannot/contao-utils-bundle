<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Cache;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Cache\FileCache;
use HeimrichHannot\UtilsBundle\File\FileUtil;

class FileCacheTest extends ContaoTestCase
{
    protected $cacheFolder;
    protected $webFolder;

    protected function setUp()
    {
        parent::setUp();
        $folder = $this->getTempDir().'/huhutilsbundle/'.uniqid();
        $this->cacheFolder = $folder.'/cache';
        $this->webFolder = $folder.'/web';
    }

    public function testCanBeInstantiated()
    {
        $fileUtil = $this->createMock(FileUtil::class);
        $fileCache = new FileCache($this->cacheFolder, $this->webFolder, $fileUtil);
        $this->assertInstanceOf(FileCache::class, $fileCache);
    }
}

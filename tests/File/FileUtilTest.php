<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\File;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Arrays\ArrayUtil;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use Symfony\Component\Filesystem\Filesystem;

class FileUtilTest extends ContaoTestCase
{
    public static function tearDownAfterClass(): void
    {
        // The temporary directory would not be removed without this call!
        parent::tearDownAfterClass();
    }

    public function setUp()
    {
        parent::setUp();

        if (!defined('TL_ROOT')) {
            \define('TL_ROOT', __DIR__);
        }

        $fs = new Filesystem();
        $fs->mkdir($this->getTempDir().'/files/');

        $arrayUtils = new ArrayUtil($this->mockContaoFramework());
        $container = $this->mockContainer();
        $container->set('huh.utils.array', $arrayUtils);
        System::setContainer($container);
    }

    public function testGetFileList()
    {
        file_put_contents($this->getTempDir().'/files/testfile1', 'test');
        file_put_contents($this->getTempDir().'/files/testfile2', 'test');
        file_put_contents($this->getTempDir().'/files/testfile3', 'test');

        $framework = $this->mockContaoFramework();
        $fileUtil = new FileUtil($framework);
        $fileList = $fileUtil->getFileList($this->getTempDir().'/files', __DIR__);

        $this->assertCount(3, $fileList);
        $this->assertArrayHasKey(0, $fileList);
        $this->assertArrayHasKey('filename', $fileList[0]);
        $this->assertSame('testfile3', $fileList[0]['filename']);
        $this->assertArrayHasKey(1, $fileList);
        $this->assertArrayHasKey('filename', $fileList[1]);
        $this->assertSame('testfile2', $fileList[1]['filename']);
        $this->assertArrayHasKey(2, $fileList);
        $this->assertArrayHasKey('filename', $fileList[2]);
        $this->assertSame('testfile1', $fileList[2]['filename']);

        $fileList = $fileUtil->getFileList($this->getTempDir().'/fileList', __DIR__);

        $this->assertCount(0, $fileList);
    }

    public function testGetUniqueFileNameWithinTarget()
    {
        $framework = $this->mockContaoFramework();
        $fileUtil = new FileUtil($framework);
        $fileName = $fileUtil->getUniqueFileNameWithinTarget($this->getTempDir().'/files/testfile.txt');

        $this->assertFalse($fileName);
    }

    public function testFormatSizeUnits()
    {
        $framework = $this->mockContaoFramework();
        $fileUtil = new FileUtil($framework);

        $bytes = $fileUtil->formatSizeUnits(1073741824);
        $this->assertSame('1.00 GB', $bytes);
        $bytes = $fileUtil->formatSizeUnits(1048576);
        $this->assertSame('1.00 MB', $bytes);
        $bytes = $fileUtil->formatSizeUnits(1024);
        $this->assertSame('1.00 KB', $bytes);
        $bytes = $fileUtil->formatSizeUnits(3);
        $this->assertSame('3 Bytes', $bytes);
        $bytes = $fileUtil->formatSizeUnits(1);
        $this->assertSame('1 Byte', $bytes);
        $bytes = $fileUtil->formatSizeUnits(10737.41824);
        $this->assertSame('10.49 KB', $bytes);
        try {
            $bytes = $fileUtil->formatSizeUnits('107374,1824');
        } catch (\Exception $exception) {
            $this->assertSame('A non well formed numeric value encountered', $exception->getMessage());
        }
        $bytes = $fileUtil->formatSizeUnits(1073741894, true);
        $this->assertSame('1.00&nbsp;GB', $bytes);
        $bytes = $fileUtil->formatSizeUnits(0.1073741894, true);
        $this->assertSame('0&nbsp;Bytes', $bytes);
    }

    public function testGetPathWithoutFilename()
    {
        $framework = $this->mockContaoFramework();
        $fileUtil = new FileUtil($framework);
        $path = $fileUtil->getPathWithoutFilename($this->getTempDir().'/file/testfile1');
        $this->assertSame($this->getTempDir().'/file', $path);

        $path = $fileUtil->getPathWithoutFilename('');
        $this->assertSame('', $path);

        $path = $fileUtil->getPathWithoutFilename(1234);
        $this->assertSame('.', $path);
    }

    public function testGetFileExtension()
    {
        $framework = $this->mockContaoFramework();
        $fileUtil = new FileUtil($framework);
        $fileExtension = $fileUtil->getFileExtension($this->getTempDir().'/file/testfile1');
        $this->assertSame('', $fileExtension);
        $fileExtension = $fileUtil->getFileExtension($this->getTempDir().'/file/testfile1.txt');
        $this->assertSame('txt', $fileExtension);
        $fileExtension = $fileUtil->getFileExtension($this->getTempDir().'/file/testfile1.xml');
        $this->assertSame('xml', $fileExtension);
        $fileExtension = $fileUtil->getFileExtension($this->getTempDir().'/file/testfile1...xml');
        $this->assertSame('xml', $fileExtension);
        $fileExtension = $fileUtil->getFileExtension($this->getTempDir().'');
        $this->assertSame('', $fileExtension);
        $fileExtension = $fileUtil->getFileExtension($this->getTempDir().'.xml');
        $this->assertSame('xml', $fileExtension);
        $fileExtension = $fileUtil->getFileExtension('');
        $this->assertSame('', $fileExtension);
        $fileExtension = $fileUtil->getFileExtension(1234);
        $this->assertSame('', $fileExtension);
    }

    public function testAddUniqueIdToFilename()
    {
        $framework = $this->mockContaoFramework();
        $fileUtil = new FileUtil($framework);

        $file = $fileUtil->addUniqueIdToFilename('testFile');
        $this->assertNotSame('testFile', $file);
    }
}

<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\File;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\File\FileStorage;
use HeimrichHannot\UtilsBundle\File\FileStorageCallback;
use Symfony\Component\Filesystem\Filesystem;

class FileStorageTest extends ContaoTestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function testGet()
    {
        $files = new Filesystem();

        $tempFolder = 'filestorage';
        $instance = new FileStorage($this->getTempDir(), $tempFolder);
        $files->dumpFile($this->getTempDir().'/'.$tempFolder.'/test', 'hallo');
        $this->assertSame($tempFolder.'/test', $instance->get('test'));
        $this->assertSame($tempFolder.'/test', $instance->get('Test'));
        $this->assertNull($instance->get('t€st'));
    }

    public function testSet()
    {
        $files = new Filesystem();

        $tempFolder = 'filestorage';
        $instance = new FileStorage($this->getTempDir(), $tempFolder);
        $path = $instance->set('Test 1', 'Test 1');
        $this->assertSame(file_get_contents($this->getTempDir().'/'.$tempFolder.'/test-1'), 'Test 1');
        $path = $instance->set('Test 1', 'Test 1.1');
        $this->assertTrue($files->exists($this->getTempDir().'/'.$tempFolder.'/test-1'));
        $this->assertSame(file_get_contents($this->getTempDir().'/'.$tempFolder.'/test-1'), 'Test 1.1');
        $this->assertStringStartsWith($tempFolder, $instance->get('Test 1'));
        $this->assertStringStartsWith($tempFolder, $instance->get('test-1'));

        $tempFolder = 'filestoragecallback';
        $instance = new FileStorage($this->getTempDir(), $tempFolder);
        $path = $instance->set('Test 1', function (FileStorageCallback $fileStorageCallback) {
            $filesystem = new Filesystem();
            $filesystem->dumpFile($fileStorageCallback->getAbsoluteFilePath(), 'Hello World!');

            return true;
        });
        $this->assertStringStartsWith($tempFolder, $path);
        $this->assertTrue($files->exists($this->getTempDir().'/'.$tempFolder.'/test-1'));
        $this->assertSame('Hello World!', file_get_contents($this->getTempDir().'/'.$tempFolder.'/test-1'));

        $tempFolder = 'filestorage';
        $instance = new FileStorage($this->getTempDir(), $tempFolder, 'txt');
        $path = $instance->set('Test 1', 'Test 1');
        $this->assertSame(file_get_contents($this->getTempDir().'/'.$tempFolder.'/test-1.txt'), 'Test 1');
        $path = $instance->set('Test 1', 'Test 1.1', ['fileExtension' => 'md']);
        $this->assertTrue($files->exists($this->getTempDir().'/'.$tempFolder.'/test-1.md'));
        $this->assertSame(file_get_contents($this->getTempDir().'/'.$tempFolder.'/test-1.md'), 'Test 1.1');
        $this->assertStringStartsWith($tempFolder, $instance->get('Test 1'));
        $this->assertStringStartsWith($tempFolder, $instance->get('test-1'));

        $tempFolder = 'filestoragecallback';
        $instance = new FileStorage($this->getTempDir(), $tempFolder);

        $unexpectedExceptionThrown = false;

        try {
            $path = $instance->set('Test 1', function (FileStorageCallback $fileStorageCallback) {
                $filesystem = new Filesystem();
                $filesystem->dumpFile($fileStorageCallback->getAbsoluteFilePath(), 'Hello World!');
            });
        } catch (\UnexpectedValueException $e) {
            $unexpectedExceptionThrown = true;
        }
        $this->assertTrue($unexpectedExceptionThrown);

        $unexpectedArgumentThrown = false;

        try {
            $path = $instance->set('Test 1', ['Hello' => 'World']);
        } catch (\InvalidArgumentException $e) {
            $unexpectedArgumentThrown = true;
        }
        $this->assertTrue($unexpectedArgumentThrown);
    }
}

<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Cache;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Cache\FileCache;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FileCacheTest extends ContaoTestCase
{
    protected $cacheFolder;
    protected $webFolder;

    protected function setUp()
    {
        parent::setUp();

        $GLOBALS['TL_CONFIG']['characterSet'] = 'UTF-8';
    }

    public function testCanBeInstantiated()
    {
        $fileCache = new FileCache($this->getContainerMock());
        $this->assertInstanceOf(FileCache::class, $fileCache);
    }

    public function testExists()
    {
        $container = $this->getContainerMock();
        $fileCache = new FileCache($container);
        System::setContainer($container);
        file_put_contents($this->getTempDir().'/cache/test', 'hallo');
        $this->assertTrue($fileCache->exist('test'));
        $this->assertFalse($fileCache->exist('testABC'));

        $fileCache->setNamespace('phpunit');
        file_put_contents($this->getTempDir().'/cache/phpunit/test2', 'hallo');
        $this->assertTrue($fileCache->exist('test2'));
        $this->assertFalse($fileCache->exist('test'));
        $fileCache->setNamespace('');
        $this->assertTrue($fileCache->exist('test'));
        $this->assertFalse($fileCache->exist('test2'));

        file_put_contents($this->getTempDir().'/cache/test3.txt', 'hallo');
        $this->assertTrue($fileCache->exist('test3', 'txt'));
        $this->assertFalse($fileCache->exist('test3'));
        $this->assertFalse($fileCache->exist('test3', 'doc'));
        $this->assertFalse($fileCache->exist('test', 'txt'));
    }

    public function testGet()
    {
        $container = $this->getContainerMock();
        $fileCache = new FileCache($container);
        System::setContainer($container);
        file_put_contents($this->getTempDir().'/cache/test', 'hallo');
        $file = $fileCache->get('test');
        $this->assertSame('hallo', file_get_contents($this->getTempDir().'/'.$file));
        $this->assertFalse($fileCache->get('test123'));

        file_put_contents($this->getTempDir().'/cache/test.doc', 'hallo');
        $file = $fileCache->get('test', 'doc');
        $this->assertSame('hallo', file_get_contents($this->getTempDir().'/'.$file));
        $this->assertFalse($fileCache->get('test123'));

        $file = $fileCache->get('test123', '', function ($identifier, $cacheFolderWithNamespace, $fileName) {
            file_put_contents($this->getTempDir().'/'.$cacheFolderWithNamespace.'/'.$fileName, 'callback');

            return true;
        });
        $this->assertSame('callback', file_get_contents($this->getTempDir().'/'.$file));
    }

    public function testGenerateCacheName()
    {
        $container = $this->getContainerMock();
        $fileCache = new FileCache($container);
        System::setContainer($container);

        $this->assertSame('test', $fileCache->generateCacheName('test'));
        $this->assertSame('test', $fileCache->generateCacheName('Test'));
        $this->assertSame('hallo-welt', $fileCache->generateCacheName('Hallo Welt'));

        $this->assertSame('test', $fileCache->generateCacheName('test', 'prefix'));
        $this->assertSame('test', $fileCache->generateCacheName('test', 'prefix', false));
        $this->assertSame('test.jpg', $fileCache->generateCacheName('test', 'prefix', false, 'jpg'));

        $this->assertSame(23, \strlen($fileCache->generateCacheName()));
        $this->assertSame(13, \strlen($fileCache->generateCacheName('', '', false)));
        $this->assertSame(19, \strlen($fileCache->generateCacheName('', 'prefix', false)));
        $this->assertSame(29, \strlen($fileCache->generateCacheName('', 'prefix')));
        $this->assertSame(33, \strlen($fileCache->generateCacheName('', 'prefix', true, 'jpg')));
    }

    public function testGetFilePath()
    {
        $container = $this->getContainerMock();
        $fileCache = new FileCache($container);
        System::setContainer($container);

        $this->assertSame('cache/test', $fileCache->getCacheFilePath('test'));
        $fileCache->setNamespace('abc');
        $this->assertSame('cache/abc/test', $fileCache->getCacheFilePath('Test'));
        $this->assertSame('cache/abc/hallo-welt', $fileCache->getCacheFilePath('Hallo Welt'));

        $fileCache->setNamespace('');
        $this->assertSame(29, \strlen($fileCache->getCacheFilePath()));
    }

    public function testGetAbsoluteCachePath()
    {
        $container = $this->getContainerMock();
        $fileCache = new FileCache($container);
        System::setContainer($container);

        $this->assertSame($this->getTempDir().'/'.'cache', $fileCache->getAbsoluteCachePath());
    }

    public function testGetNamespace()
    {
        $container = $this->getContainerMock();
        $fileCache = new FileCache($container);
        System::setContainer($container);

        $this->assertEmpty($fileCache->getNamespace());
        $fileCache->setNamespace('test');
        $this->assertSame('test', $fileCache->getNamespace());
        $fileCache->setNamespace('/test');
        $this->assertSame('test', $fileCache->getNamespace());
        $fileCache->setNamespace('');
        $this->assertEmpty($fileCache->getNamespace());
    }

    public function testGetCacheFolder()
    {
        $container = $this->getContainerMock();
        $fileCache = new FileCache($container);
        System::setContainer($container);

        $this->assertSame('cache', $fileCache->getCacheFolder());
    }

    public function testSetCacheFolder()
    {
        $container = $this->getContainerMock();
        $fileCache = new FileCache($container);
        System::setContainer($container);

        $fileCache->setCacheFolder('new_cache');
        $this->assertTrue(is_dir($this->getTempDir().'/new_cache'));
        $this->assertSame('new_cache', $fileCache->getCacheFolder());
    }

    public function testGetCacheFolderWithNamespace()
    {
        $container = $this->getContainerMock();
        $fileCache = new FileCache($container);
        System::setContainer($container);

        $this->assertSame('cache', $fileCache->getCacheFolderWithNamespace());
        $fileCache->setNamespace('namespace');
        $this->assertSame('cache/namespace', $fileCache->getCacheFolderWithNamespace());
    }

    protected function getContainerMock(ContainerBuilder $container = null)
    {
        if (!$container) {
            $container = $this->mockContainer($this->getTempDir());
        }
        $container->setParameter('huh.utils.filecache.folder', 'cache');
        $container->set('huh.utils.file', $this->createMock(FileUtil::class));

        return $container;
    }
}

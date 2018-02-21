<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Cache;

use HeimrichHannot\UtilsBundle\Cache\RemoteImageCache;
use HeimrichHannot\UtilsBundle\Curl\CurlUtil;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Tests\TestCaseEnvironment;
use Symfony\Component\Filesystem\Filesystem;

class RemoteImageCacheTest extends TestCaseEnvironment
{
    protected $tempPath;

    public function setUp()
    {
        parent::setUp();

//        if (!defined('TL_ROOT')) {
//            define('TL_ROOT', __DIR__);
//        }

        $key = basename(strtr(static::class, '\\', '/'));
        $this->tempPath = uniqid($key.'_');

        $fs = new Filesystem();
        $fs->mkdir(TL_ROOT.DIRECTORY_SEPARATOR.$this->tempPath);
        $fs->mkdir(TL_ROOT.DIRECTORY_SEPARATOR.'system/tmp');
    }

    public function tearDown()
    {
        parent::tearDown();

        $fs = new Filesystem();
        $fs->remove(TL_ROOT.DIRECTORY_SEPARATOR.$this->tempPath);
        $fs->remove(TL_ROOT.DIRECTORY_SEPARATOR.'system');
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $framework = $this->mockContaoFramework();
        $container = $this->prepareContainer($framework);

        $curlMock = $this->createMock(CurlUtil::class);
        $curlMock->method('request')->willReturnCallback(function ($argument) {
            switch ($argument) {
                case 'remoteNull':
                    return null;
                case 'remoteFalse':
                    return false;
                case 'remoteEmpty':
                    return 'null';
                case 'remoteImage':
                default:
                    return 'validImage';
            }
        });

        $container->set('huh.utils.curl', $curlMock);
        $instance = new RemoteImageCache($framework, $container);
        $this->assertInstanceOf(RemoteImageCache::class, $instance);
    }

    public function testGet()
    {
        $framework = $this->mockContaoFramework();
        $container = $this->prepareContainer($framework);

        $path = TL_ROOT.DIRECTORY_SEPARATOR.$this->tempPath;
        $testFile = $path.'/test01.jpg';

        $fs = new Filesystem();
        $fs->dumpFile($testFile, 'test01');

        $cache = new RemoteImageCache($framework, $container);

        $this->assertSame($this->tempPath.'/test01.jpg', $cache->get('test01', $this->tempPath, 'http://www.google.de'));
        $this->assertSame($this->tempPath.'/test01.jpg', $cache->get('test01', 'fade6980-1641-11e8-b642-0ed5f89f718b', 'http://www.google.de'));
        $this->assertFalse($cache->get('test01', '0c23ab88-1642-11e8-b642-0ed5f89f718b', 'http://www.google.de'));

        $this->assertSame($this->tempPath.'/test02.jpg', $cache->get('test02', $this->tempPath, 'http://www.google.de'));
    }

    public function prepareContainer($framework)
    {
        $container = $this->mockContainer();

        $container->set('contao.framework', $framework);

        $curlMock = $this->createMock(CurlUtil::class);
        $curlMock->method('request')->willReturnCallback(function ($argument) {
            switch ($argument) {
                case 'remoteNull':
                    return null;
                case 'remoteFalse':
                    return false;
                case 'remoteEmpty':
                    return 'null';
                case 'remoteImage':
                default:
                    return 'test02';
            }
        });
        $container->set('huh.utils.curl', $curlMock);

        $fileUtilMock = $this->createMock(FileUtil::class);
        $fileUtilMock->method('getFolderFromUuid')->willReturnCallback(function ($argument) {
            switch ($argument) {
                case '0c23ab88-1642-11e8-b642-0ed5f89f718b':
                    return false;
                case 'fade6980-1641-11e8-b642-0ed5f89f718b':
                default:
                    $folder = new \stdClass();
                    $folder->value = $this->tempPath;

                    return $folder;
            }
        });
        $container->set('huh.utils.file', $fileUtilMock);

        return $container;
    }
}

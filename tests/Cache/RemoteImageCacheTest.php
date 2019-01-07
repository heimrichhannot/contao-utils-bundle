<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Cache;

use Contao\FilesModel;
use Contao\System;
use HeimrichHannot\UtilsBundle\Cache\RemoteImageCache;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Request\CurlRequestUtil;
use HeimrichHannot\UtilsBundle\Tests\TestCaseEnvironment;
use Symfony\Component\Filesystem\Filesystem;

class RemoteImageCacheTest extends TestCaseEnvironment
{
    protected $tempPath;

    public function setUp()
    {
        parent::setUp();

        if (!\defined('TL_ROOT')) {
            \define('TL_ROOT', __DIR__);
        }

        $key = basename(strtr(static::class, '\\', '/'));
        $this->tempPath = uniqid($key.'_');

        $fs = new Filesystem();
        $fs->mkdir(TL_ROOT.\DIRECTORY_SEPARATOR.$this->tempPath);
        $fs->mkdir(TL_ROOT.\DIRECTORY_SEPARATOR.'system/tmp');
    }

    protected function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove(TL_ROOT.\DIRECTORY_SEPARATOR.$this->tempPath);
        $fs->remove(TL_ROOT.\DIRECTORY_SEPARATOR.'system');
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $framework = $this->mockContaoFramework();
        $container = $this->prepareContainer($framework);

        $curlMock = $this->createMock(CurlRequestUtil::class);
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

        System::getContainer()->set('huh.utils.curl', $curlMock);

        $instance = new RemoteImageCache($framework);
        $this->assertInstanceOf(RemoteImageCache::class, $instance);
    }

    public function testGet()
    {
        $path = TL_ROOT.\DIRECTORY_SEPARATOR.$this->tempPath;
        $testFile = $path.'/test01.jpg';

        $filesModel = new \stdClass();
        $filesModel->path = str_replace(TL_ROOT.\DIRECTORY_SEPARATOR, '', $path);
        $filesModel->uuid = 'fade6980-1641-11e8-b642-0ed5f89f718b';

        $filesModelAdapter = $this->mockAdapter(['findByUuid']);
        $filesModelAdapter->method('findByUuid')->willReturn($filesModel);

        $curlMock = $this->createMock(CurlRequestUtil::class);
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
                    return 'test01';
            }
        });

        System::getContainer()->set('huh.utils.request.curl', $curlMock);

        $framework = $this->mockContaoFramework([
            FilesModel::class => $filesModelAdapter,
        ]);

        System::getContainer()->set('huh.utils.file', new FileUtil($framework));

        $fs = new Filesystem();
        $fs->dumpFile($testFile, 'test01');

        $cache = new RemoteImageCache($framework);

        $this->assertSame(
            $this->tempPath.'/test01.jpg',
            $cache->get('test01', $this->tempPath, 'http://www.google.de')
        );
        $this->assertSame(
            $this->tempPath.'/test01.jpg',
            $cache->get('test01', 'fade6980-1641-11e8-b642-0ed5f89f718b', 'http://www.google.de')
        );

        $this->assertSame(
            $this->tempPath.'/test02.jpg',
            $cache->get('test02', $this->tempPath, 'http://www.google.de')
        );

        $this->assertFalse(
            $cache->get('test03', $this->tempPath, 'remoteFalse')
        );

        $filesModelAdapter = $this->mockAdapter(['findByUuid']);
        $filesModelAdapter->method('findByUuid')->willReturn(null);

        $framework = $this->mockContaoFramework([
            FilesModel::class => $filesModelAdapter,
        ]);

        System::getContainer()->set('huh.utils.file', new FileUtil($framework));

        $this->assertFalse($cache->get('test01', '0c23ab88-1642-11e8-b642-0ed5f89f718b', 'http://www.google.de'));
    }

    public function prepareContainer($framework)
    {
        $container = $this->mockContainer();

        $container->set('contao.framework', $framework);

        $curlMock = $this->createMock(CurlRequestUtil::class);
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

        System::getContainer()->set('huh.utils.request.curl', $curlMock);

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

        System::getContainer()->set('huh.utils.file', $fileUtilMock);

        return $container;
    }
}

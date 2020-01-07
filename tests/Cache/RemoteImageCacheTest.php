<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Cache;

use Contao\FilesModel;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Cache\RemoteImageCache;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Request\CurlRequestUtil;
use HeimrichHannot\UtilsBundle\Tests\ResetContaoSingletonTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RemoteImageCacheTest extends ContaoTestCase
{
    use ResetContaoSingletonTrait;

    /**
     * @var string
     */
    protected $projectRoot;

    public function setUp()
    {
        parent::setUp();

        $this->projectRoot = $this->getTempDir();

        $fs = new Filesystem();
        $fs->mkdir($this->projectRoot.'/tmp');
        $fs->mkdir($this->projectRoot.'/system/tmp');
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $instance = new RemoteImageCache($this->getContainerMock());
        $this->assertInstanceOf(RemoteImageCache::class, $instance);
    }

    public function testGet()
    {
        $container = $this->getContainerMock();
        $this->resetFilesInstance($container);
        System::setContainer($container);

        $path = $this->projectRoot.'/tmp';
        $testFile = $path.'/test01.jpg';

        $filesModel = new \stdClass();
        $filesModel->path = str_replace($this->projectRoot.\DIRECTORY_SEPARATOR, '', $path);
        $filesModel->uuid = 'fade6980-1641-11e8-b642-0ed5f89f718b';

        $filesModelAdapter = $this->mockAdapter(['findByUuid']);
        $filesModelAdapter->method('findByUuid')->willReturn($filesModel);

        $framework = $this->mockContaoFramework([
                FilesModel::class => $filesModelAdapter,
        ]);

        $container->set('contao.framework', $framework);

        $container->set('huh.utils.file', new FileUtil($container));

        $fs = new Filesystem();
        $fs->dumpFile($testFile, 'test01');

        $cache = new RemoteImageCache($container);

        $this->assertSame(
            'tmp/test01.jpg',
            $cache->get('test01', 'tmp', 'http://www.google.de')
        );
        $this->assertSame(
            'tmp/test01.jpg',
            $cache->get('test01', 'fade6980-1641-11e8-b642-0ed5f89f718b', 'http://www.google.de')
        );

        $this->assertSame(
            'tmp/test02.jpg',
            $cache->get('test02', 'tmp', 'http://www.google.de')
        );

        $this->assertFalse(
            $cache->get('test03', 'tmp', 'remoteFalse')
        );

        $filesModelAdapter = $this->mockAdapter(['findByUuid']);
        $filesModelAdapter->method('findByUuid')->willReturn(null);

        $framework = $this->mockContaoFramework([
                FilesModel::class => $filesModelAdapter,
        ]);
        $container->set('contao.framework', $framework);

        $container->set('huh.utils.file', new FileUtil($container));

        $this->assertFalse($cache->get('test01', '0c23ab88-1642-11e8-b642-0ed5f89f718b', 'http://www.google.de'));
    }

    protected function getContainerMock(ContainerBuilder $container = null)
    {
        if (!$container) {
            $container = $this->mockContainer($this->projectRoot);
        }

        $requestStack = new RequestStack();
        $request = new Request();
        $request->attributes->set('_contao_referer_id', 'foobar');
        $requestStack->push($request);
        $container->set('request_stack', $requestStack);

        System::setContainer($container);

        $container->set('contao.framework', $this->mockContaoFramework());

        $curlMock = $this->createMock(CurlRequestUtil::class);
        $curlMock->method('request')->willReturnCallback(
            function ($argument) {
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
            }
        );
        $container->set('huh.utils.request.curl', $curlMock);

        $fileUtilMock = $this->createMock(FileUtil::class);
        $fileUtilMock->method('getFolderFromUuid')->willReturnCallback(
            function ($argument) {
                switch ($argument) {
                    case '0c23ab88-1642-11e8-b642-0ed5f89f718b':
                        return false;

                    case 'fade6980-1641-11e8-b642-0ed5f89f718b':
                    default:
                        $folder = new \stdClass();
                        $folder->value = 'tmp';

                        return $folder;
                }
            }
        );
        $container->set('huh.utils.file', $fileUtilMock);

        return $container;
    }
}

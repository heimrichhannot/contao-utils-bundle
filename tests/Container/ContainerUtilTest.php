<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Container;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\HeimrichHannotContaoUtilsBundle;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\HttpKernel\Log\Logger;

class ContainerUtilTest extends ContaoTestCase
{
    const FILE_LOCATOR_BUNDLE_PATH = 'vendor/heimrichhannot/contao-utils-bundle';

    public function setUp()
    {
        parent::setUp();

        $container = $this->mockContainer('projectDir');
        $container->setParameter('kernel.bundles', [ContaoCoreBundle::class]);
        $container->setParameter('contao.web_dir', 'webDir');
        $container->set('request_stack', $this->createRequestStackMock());

        $container->set('monolog.logger.contao', new Logger());

        System::setContainer($container);
    }

    public function testCanBeInstantiated()
    {
        $containerUtil = $this->createContainerUtilMock();
        $this->assertInstanceOf(ContainerUtil::class, $containerUtil);
    }

    public function testGetActiveBundles()
    {
        $containerUtil = $this->createContainerUtilMock();
        $bundles = $containerUtil->getActiveBundles();
        $this->assertSame([ContaoCoreBundle::class], $bundles);
    }

    public function testIsBundleActive()
    {
        $containerUtil = $this->createContainerUtilMock();
        $result = $containerUtil->isBundleActive(ContaoCoreBundle::class);
        $this->assertTrue($result);
    }

    public function testGetCurrentRequest()
    {
        $containerUtil = $this->createContainerUtilMock();
        $request = $containerUtil->getCurrentRequest();
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\Request::class, $request);
    }

    public function testGetProjectDir()
    {
        $containerUtil = $this->createContainerUtilMock();
        $projectDir = $containerUtil->getProjectDir();
        $this->assertSame('projectDir', $projectDir);
    }

    public function testGetWebDir()
    {
        $containerUtil = $this->createContainerUtilMock();
        $webDir = $containerUtil->getWebDir();
        $this->assertSame('webDir', $webDir);
    }

    public function testIsBackend()
    {
        $containerUtil = $this->createContainerUtilMock();
        $result = $containerUtil->isBackend();
        $this->assertTrue($result);
    }

    public function testIsFrontend()
    {
        $containerUtil = $this->createContainerUtilMock();
        $result = $containerUtil->isFrontend();
        $this->assertTrue($result);
    }

    public function testIsFrontendBackendFalse()
    {
        $adapter = $this->mockAdapter(['getCurrentRequest']);
        $adapter->method('getCurrentRequest')->willReturn(false);

        $container = System::getContainer();
        $container->set('request_stack', $adapter);
        System::setContainer($container);

        $containerUtil = $this->createContainerUtilMock();
        $result = $containerUtil->isFrontend();
        $this->assertFalse($result);
        $result = $containerUtil->isBackend();
        $this->assertFalse($result);
    }

    public function testLog()
    {
        $utils = $this->createContainerUtilMock();
        try {
            $utils->log('log', '', 'WARNING');
        } catch (\Exception $exception) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        }
    }

    public function testMergeConfigFile()
    {
        $configFile = ['config' => 'config'];

        $config = ContainerUtil::mergeConfigFile('yml', 'yml', $configFile, TL_ROOT.'/../src/Resources/config/services.yml');
        $this->assertNotSame($configFile, $config);
        $this->assertArrayHasKey('services', $config);
        $this->assertArrayHasKey('huh.utils.array', $config['services']);
    }

    public function createRequestStackMock()
    {
        $requestStack = new RequestStack();
        $request = new \Symfony\Component\HttpFoundation\Request();
        $request->attributes->set('_contao_referer_id', 'foobar');
        $requestStack->push($request);

        return $requestStack;
    }

    public function testGetBundleResourcePath()
    {
        $containerUtil = $this->createContainerUtilMock();
        $this->assertFalse($containerUtil->getBundleResourcePath(5, 'Resources/views/image.html.twig'));
        $this->assertSame(static::FILE_LOCATOR_BUNDLE_PATH, $containerUtil->getBundleResourcePath(HeimrichHannotContaoUtilsBundle::class));
        $this->assertFalse($containerUtil->getBundleResourcePath(FileLocatorFileNotFoundException::class, 'Resources/views/image.html.twig'));
        $this->assertFalse($containerUtil->getBundleResourcePath(\InvalidArgumentException::class, 'Resources/views/image.html.twig'));
        $this->assertSame([static::FILE_LOCATOR_BUNDLE_PATH.'/Resources/views/image.html.twig'], $containerUtil->getBundleResourcePath(HeimrichHannotContaoUtilsBundle::class, 'Resources/views/image.html.twig'));
        $this->assertSame(static::FILE_LOCATOR_BUNDLE_PATH.'/Resources/views/image.html.twig', $containerUtil->getBundleResourcePath(HeimrichHannotContaoUtilsBundle::class, 'Resources/views/image.html.twig', true));
    }

    public function testGetBundlePath()
    {
        $containerUtil = $this->createContainerUtilMock();
        $this->assertFalse($containerUtil->getBundlePath('No Path'));
        $this->assertFalse($containerUtil->getBundlePath(5));
        $this->assertSame(static::FILE_LOCATOR_BUNDLE_PATH, $containerUtil->getBundlePath(HeimrichHannotContaoUtilsBundle::class));
    }

    protected function createContainerUtilMock()
    {
        $fileLocatorMock = $this->createMock(FileLocator::class);
        $fileLocatorMock->method('locate')->willReturnCallback(function ($path, $currentPath = null, $first = false) {
            switch ($path) {
                case '@HeimrichHannotContaoUtilsBundle':
                    return static::FILE_LOCATOR_BUNDLE_PATH;
                case '@HeimrichHannotContaoUtilsBundle/Resources/views/image.html.twig':
                    $result = static::FILE_LOCATOR_BUNDLE_PATH.'/Resources/views/image.html.twig';
                    break;
                case \InvalidArgumentException::class:
                    throw new \InvalidArgumentException();
                default:
                    throw new FileLocatorFileNotFoundException();
            }
            if ($first) {
                return $result;
            }

            return [$result];
        });

        $scopeAdapter = $this->createMock(ScopeMatcher::class);
        $scopeAdapter->method('isBackendRequest')->willReturn(true);
        $scopeAdapter->method('isFrontendRequest')->willReturn(true);

        $containerUtil = new ContainerUtil($this->mockContaoFramework(), $fileLocatorMock, $scopeAdapter);

        return $containerUtil;
    }
}

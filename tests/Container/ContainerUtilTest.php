<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Container;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Log\Logger;

class ContainerUtilTest extends ContaoTestCase
{
    public function setUp()
    {
        parent::setUp();

        $container = $this->mockContainer('projectDir');
        $container->setParameter('kernel.bundles', [ContaoCoreBundle::class]);
        $container->setParameter('contao.web_dir', 'webDir');
        $container->set('request_stack', $this->createRequestStackMock());

        $scopeAdapter = $this->mockAdapter(['isBackendRequest', 'isFrontendRequest']);
        $scopeAdapter->method('isBackendRequest')->willReturn(true);
        $scopeAdapter->method('isFrontendRequest')->willReturn(true);
        $container->set('contao.routing.scope_matcher', $scopeAdapter);

        $container->set('monolog.logger.contao', new Logger());

        System::setContainer($container);
    }

    public function testCanBeInstantiated()
    {
        $containerUtil = new ContainerUtil($this->mockContaoFramework());
        $this->assertInstanceOf(ContainerUtil::class, $containerUtil);
    }

    public function testGetActiveBundles()
    {
        $containerUtil = new ContainerUtil($this->mockContaoFramework());
        $bundles = $containerUtil->getActiveBundles();
        $this->assertSame([ContaoCoreBundle::class], $bundles);
    }

    public function testIsBundleActive()
    {
        $containerUtil = new ContainerUtil($this->mockContaoFramework());
        $result = $containerUtil->isBundleActive(ContaoCoreBundle::class);
        $this->assertTrue($result);
    }

    public function testGetCurrentRequest()
    {
        $containerUtil = new ContainerUtil($this->mockContaoFramework());
        $request = $containerUtil->getCurrentRequest();
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\Request::class, $request);
    }

    public function testGetProjectDir()
    {
        $containerUtil = new ContainerUtil($this->mockContaoFramework());
        $projectDir = $containerUtil->getProjectDir();
        $this->assertSame('projectDir', $projectDir);
    }

    public function testGetWebDir()
    {
        $containerUtil = new ContainerUtil($this->mockContaoFramework());
        $webDir = $containerUtil->getWebDir();
        $this->assertSame('webDir', $webDir);
    }

    public function testIsBackend()
    {
        $containerUtil = new ContainerUtil($this->mockContaoFramework());
        $result = $containerUtil->isBackend();
        $this->assertTrue($result);
    }

    public function testIsFrontend()
    {
        $containerUtil = new ContainerUtil($this->mockContaoFramework());
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

        $containerUtil = new ContainerUtil($this->mockContaoFramework());
        $result = $containerUtil->isFrontend();
        $this->assertFalse($result);
        $result = $containerUtil->isBackend();
        $this->assertFalse($result);
    }

    public function testLog()
    {
        $utils = new ContainerUtil($this->mockContaoFramework());
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
        $this->assertCount(26, $config['services']);
    }

    public function createRequestStackMock()
    {
        $requestStack = new RequestStack();
        $request = new \Symfony\Component\HttpFoundation\Request();
        $request->attributes->set('_contao_referer_id', 'foobar');
        $requestStack->push($request);

        return $requestStack;
    }
}

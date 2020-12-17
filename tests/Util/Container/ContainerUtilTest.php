<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Util\Container;

use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Input;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Util\Container\ContainerUtil;
use Psr\Log\LogLevel;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;

class ContainerUtilTest extends ContaoTestCase
{
    public function getTestInstance(array $parameters = [])
    {
        if (!isset($parameters['locator'])) {
            $parameters['locator'] = $parameters['locator'] = $this->createMock(ServiceLocator::class);
            $parameters['locator']->method('get')->willReturnCallback(function ($id) {
                switch ($id) {
                }
            });
        }

        if (!isset($parameters['kernelBundles'])) {
            $parameters['kernelBundles'] = [];
        }

        if (!isset($parameters['kernel'])) {
            $parameters['kernel'] = $this->createMock(KernelInterface::class);
        }

        if (!isset($parameters['framework'])) {
            $parameters['framework'] = $this->mockContaoFramework();
        }

        if (!isset($parameters['scopeMather'])) {
            $parameters['scopeMather'] = $this->createMock(ScopeMatcher::class);
        }

        if (!isset($parameters['requestStack'])) {
            $parameters['requestStack'] = $this->createMock(RequestStack::class);
        }

        return new ContainerUtil(
            $parameters['locator'],
            $parameters['kernelBundles'],
            $parameters['kernel'],
            $parameters['framework'],
            $parameters['scopeMather'],
            $parameters['requestStack']
        );
    }

    public function testIsBackend()
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn(null);
        $instance = $this->getTestInstance([
            'requestStack' => $requestStack,
        ]);
        $this->assertFalse($instance->isBackend());

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($this->createMock(Request::class));
        $scopeMatcher = $this->createMock(ScopeMatcher::class);
        $scopeMatcher->method('isBackendRequest')->willReturn(false);
        $instance = $this->getTestInstance([
            'requestStack' => $requestStack,
            'scopeMather' => $scopeMatcher,
        ]);
        $this->assertFalse($instance->isBackend());

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($this->createMock(Request::class));
        $scopeMatcher = $this->createMock(ScopeMatcher::class);
        $scopeMatcher->method('isBackendRequest')->willReturn(true);
        $instance = $this->getTestInstance([
            'requestStack' => $requestStack,
            'scopeMather' => $scopeMatcher,
        ]);
        $this->assertTrue($instance->isBackend());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testIsPreviewMode()
    {
        $inputMock = $this->mockAdapter(['cookie']);
        $framework = $this->mockContaoFramework([
            Input::class => $inputMock,
        ]);
        $instance = $this->getTestInstance(['framework' => $framework]);

        $this->assertFalse($instance->isPreviewMode());

        \define('BE_USER_LOGGED_IN', false);
        $this->assertFalse($instance->isPreviewMode());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testIsPreviewModeLoggedIn()
    {
        \define('BE_USER_LOGGED_IN', true);

        $inputMock = $this->mockAdapter(['cookie']);
        $framework = $this->mockContaoFramework([
            Input::class => $inputMock,
        ]);
        $instance = $this->getTestInstance(['framework' => $framework]);
        $this->assertFalse($instance->isPreviewMode());

        $inputMock = $this->mockAdapter(['cookie']);
        $inputMock->method('cookie')->willReturn(null);
        $framework = $this->mockContaoFramework([
            Input::class => $inputMock,
        ]);
        $instance = $this->getTestInstance(['framework' => $framework]);
        $this->assertFalse($instance->isPreviewMode());

        $inputMock = $this->mockAdapter(['cookie']);
        $inputMock->method('cookie')->willReturn('1');
        $framework = $this->mockContaoFramework([
            Input::class => $inputMock,
        ]);
        $instance = $this->getTestInstance(['framework' => $framework]);
        $this->assertTrue($instance->isPreviewMode());
    }

    public function testIsInstall()
    {
        $instance = $this->getTestInstance();
        $this->assertFalse($instance->isInstall());

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn(null);
        $instance = $this->getTestInstance(['requestStack' => $requestStack]);
        $this->assertFalse($instance->isInstall());

        $request = $this->createMock(Request::class);
        $request->method('get')->willReturn('custom_route');
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);
        $instance = $this->getTestInstance(['requestStack' => $requestStack]);
        $this->assertFalse($instance->isInstall());

        $request = $this->createMock(Request::class);
        $request->method('get')->willReturn('contao_install');
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);
        $instance = $this->getTestInstance(['requestStack' => $requestStack]);
        $this->assertTrue($instance->isInstall());
    }

    public function testIsFrontend()
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn(null);
        $instance = $this->getTestInstance([
            'requestStack' => $requestStack,
        ]);
        $this->assertFalse($instance->isFrontend());

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($this->createMock(Request::class));
        $scopeMatcher = $this->createMock(ScopeMatcher::class);
        $scopeMatcher->method('isFrontendRequest')->willReturn(false);
        $instance = $this->getTestInstance([
            'requestStack' => $requestStack,
            'scopeMather' => $scopeMatcher,
        ]);
        $this->assertFalse($instance->isFrontend());

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($this->createMock(Request::class));
        $scopeMatcher = $this->createMock(ScopeMatcher::class);
        $scopeMatcher->method('isFrontendRequest')->willReturn(true);
        $instance = $this->getTestInstance([
            'requestStack' => $requestStack,
            'scopeMather' => $scopeMatcher,
        ]);
        $this->assertTrue($instance->isFrontend());
    }

    public function testLog()
    {
        $logger = $this->createMock(Logger::class);
        $logger->expects($this->once())->method('log');
        $locator = $this->createMock(ServiceLocator::class);
        $locator->method('get')->willReturn($logger);
        $instance = $this->getTestInstance(['locator' => $locator]);
        $instance->log('Hallo Welt', __METHOD__, ContaoContext::ERROR);

        $logger = $this->createMock(Logger::class);
        $logger->method('log')->willReturnCallback(function ($level, $message, $context = []) {
            $this->assertSame(LogLevel::ERROR, $level);
            $this->assertSame('Hallo Welt', $message);
            $this->assertArrayHasKey('contao', $context);
            $this->assertInstanceOf(ContaoContext::class, $context['contao']);
        });
        $locator = $this->createMock(ServiceLocator::class);
        $locator->method('get')->willReturn($logger);
        $instance = $this->getTestInstance(['locator' => $locator]);
        $instance->log('Hallo Welt', __METHOD__, ContaoContext::ERROR);

        $logger = $this->createMock(Logger::class);
        $logger->method('log')->willReturnCallback(function ($level, $message, $context = []) {
            $this->assertSame(LogLevel::INFO, $level);
            $this->assertSame('Hallo Welt', $message);
            $this->assertArrayHasKey('contao', $context);
            $this->assertInstanceOf(ContaoContext::class, $context['contao']);
        });
        $locator = $this->createMock(ServiceLocator::class);
        $locator->method('get')->willReturn($logger);
        $instance = $this->getTestInstance(['locator' => $locator]);
        $instance->log('Hallo Welt', __METHOD__, ContaoContext::EMAIL);
    }

    public function testGetBundleResourcePath()
    {
    }

    public function testIsMaintenanceModeActive()
    {
    }

    public function testIsFrontendCron()
    {
    }

    public function testGetSubscribedServices()
    {
    }

    public function testIsDev()
    {
    }

    public function testIsBundleActive()
    {
    }

    public function testGetBundlePath()
    {
    }
}

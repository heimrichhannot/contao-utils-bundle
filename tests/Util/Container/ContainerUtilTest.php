<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Util\Container;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\HttpKernel\Bundle\ContaoModuleBundle;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Input;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\RequestBundle\HeimrichHannotContaoRequestBundle;
use HeimrichHannot\UtilsBundle\HeimrichHannotUtilsBundle;
use HeimrichHannot\UtilsBundle\Util\Container\ContainerUtil;
use Psr\Log\LogLevel;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\Config\FileLocator;
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
        $input = $this->mockAdapter(['cookie']);
        $input->method('cookie')->willReturn(false);
        $framework = $this->mockContaoFramework([
            Input::class => $input,
        ]);

        $instance = $this->getTestInstance([
            'framework' => $framework,
        ]);
        $this->assertFalse($instance->isPreviewMode());

        \define('BE_USER_LOGGED_IN', true);
        $this->assertFalse($instance->isPreviewMode());

        $input = $this->mockAdapter(['cookie']);
        $input->method('cookie')->willReturn(true);
        $framework = $this->mockContaoFramework([
            Input::class => $input,
        ]);
        $instance = $this->getTestInstance([
            'framework' => $framework,
        ]);
        $this->assertTrue($instance->isPreviewMode());

        $tokenChecker = $this->createMock(TokenChecker::class);
        $tokenChecker->method('isPreviewMode')->willReturnOnConsecutiveCalls(false, true);
        $locator = $this->createMock(ServiceLocator::class);
        $locator->method('has')->willReturn(true);
        $locator->method('get')->willReturnCallback(function ($id) use ($tokenChecker) {
            switch ($id) {
                case TokenChecker::class:
                    return $tokenChecker;
            }
        });
        $instance = $this->getTestInstance(['locator' => $locator]);
        $this->assertFalse($instance->isPreviewMode());
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
        $fileLocator = $this->createMock(FileLocator::class);
        $fileLocator->method('locate')->willReturnCallback(function ($file, $currentPath = null, $first = true) {
            if (!$first) {
                return ['test_path'];
            }

            return 'test_path';
        });
        $locator = $this->createMock(ServiceLocator::class);
        $locator->method('get')->willReturn($fileLocator);
        $instance = $this->getTestInstance(['locator' => $locator]);
        $this->assertNull($instance->getBundleResourcePath("\Acme\NonExistingBundle\BundleClass"));
        $this->assertSame('test_path', $instance->getBundleResourcePath(HeimrichHannotUtilsBundle::class, '', true));
        $this->assertSame(['test_path'], $instance->getBundleResourcePath(HeimrichHannotUtilsBundle::class));

        $fileLocator = $this->createMock(FileLocator::class);
        $fileLocator->method('locate')->willThrowException(new FileLocatorFileNotFoundException());
        $locator = $this->createMock(ServiceLocator::class);
        $locator->method('get')->willReturn($fileLocator);
        $instance = $this->getTestInstance(['locator' => $locator]);
        $this->assertNull($instance->getBundleResourcePath(HeimrichHannotUtilsBundle::class));
    }

    public function testIsMaintenanceModeActive()
    {
        $maintenanceDriverMock = $this->mockAdapter(['getDriver', 'isExists']);
        $maintenanceDriverMock->method('getDriver')->willReturnSelf();
        $maintenanceDriverMock->method('isExists')->willReturn(false);
        $locator = $this->createMock(ServiceLocator::class);
        $locator->method('get')->willReturn($maintenanceDriverMock);
        $instance = $this->getTestInstance(['locator' => $locator]);
        $this->assertFalse($instance->isMaintenanceModeActive());

        $maintenanceDriverMock = $this->mockAdapter(['getDriver', 'isExists']);
        $maintenanceDriverMock->method('getDriver')->willReturnSelf();
        $maintenanceDriverMock->method('isExists')->willReturn(true);
        $locator = $this->createMock(ServiceLocator::class);
        $locator->method('get')->willReturn($maintenanceDriverMock);
        $instance = $this->getTestInstance(['locator' => $locator]);
        $this->assertTrue($instance->isMaintenanceModeActive());
    }

    public function testIsFrontendCron()
    {
        $instance = $this->getTestInstance();
        $this->assertFalse($instance->isFrontendCron());

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn(null);
        $instance = $this->getTestInstance(['requestStack' => $requestStack]);
        $this->assertFalse($instance->isFrontendCron());

        $request = $this->createMock(Request::class);
        $request->method('get')->willReturn('custom_route');
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);
        $instance = $this->getTestInstance(['requestStack' => $requestStack]);
        $this->assertFalse($instance->isFrontendCron());

        $request = $this->createMock(Request::class);
        $request->method('get')->willReturn('contao_frontend_cron');
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);
        $instance = $this->getTestInstance(['requestStack' => $requestStack]);
        $this->assertTrue($instance->isFrontendCron());
    }

    public function testGetSubscribedServices()
    {
        $instance = $this->getTestInstance();
        $this->assertInternalType('array', $instance::getSubscribedServices());
    }

    public function testIsDev()
    {
        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('getEnvironment')->willReturn('dev');
        $instance = $this->getTestInstance(['kernel' => $kernel]);
        $this->assertTrue($instance->isDev());

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('getEnvironment')->willReturn('prod');
        $instance = $this->getTestInstance(['kernel' => $kernel]);
        $this->assertFalse($instance->isDev());
    }

    public function testIsBundleActive()
    {
        $kernelBundles = [
            'ContaoCoreBundle' => ContaoCoreBundle::class,
            'HeimrichHannotUtilsBundle' => HeimrichHannotUtilsBundle::class,
            'legacyModule' => ContaoModuleBundle::class,
        ];

        $instance = $this->getTestInstance(['kernelBundles' => $kernelBundles]);
        $this->assertTrue($instance->isBundleActive(ContaoCoreBundle::class));
        $this->assertTrue($instance->isBundleActive('legacyModule'));
        $this->assertFalse($instance->isBundleActive(HeimrichHannotContaoRequestBundle::class));
        $this->assertFalse($instance->isBundleActive('haste'));
    }

    public function testGetBundlePath()
    {
        $fileLocator = $this->createMock(FileLocator::class);
        $fileLocator->method('locate')->willReturnCallback(function ($file, $currentPath = null, $first = true) {
            if (!$first) {
                return ['test_path'];
            }

            return 'test_path';
        });
        $locator = $this->createMock(ServiceLocator::class);
        $locator->method('get')->willReturn($fileLocator);
        $instance = $this->getTestInstance(['locator' => $locator]);
        $this->assertNull($instance->getBundlePath("\Acme\NonExistingBundle\BundleClass"));
        $this->assertSame('test_path', $instance->getBundlePath(HeimrichHannotUtilsBundle::class));

        $fileLocator = $this->createMock(FileLocator::class);
        $fileLocator->method('locate')->willThrowException(new FileLocatorFileNotFoundException());
        $locator = $this->createMock(ServiceLocator::class);
        $locator->method('get')->willReturn($fileLocator);
        $instance = $this->getTestInstance(['locator' => $locator]);
        $this->assertNull($instance->getBundleResourcePath(HeimrichHannotUtilsBundle::class));
    }
}

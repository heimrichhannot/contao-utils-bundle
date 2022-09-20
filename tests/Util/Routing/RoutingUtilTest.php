<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Util\Routing;

use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use HeimrichHannot\UtilsBundle\Tests\AbstractUtilsTestCase;
use HeimrichHannot\UtilsBundle\Util\Routing\RoutingUtil;
use PHPUnit\Framework\MockObject\MockBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class RoutingUtilTest extends AbstractUtilsTestCase
{
    public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null)
    {
        $container = $parameters['container'] ?? $this->getContainerWithContaoConfiguration();
        $router = $parameters['router'] ?? $this->createMock(RouterInterface::class);
        $csrfTokenName = $parameters['csrfTokenName'] ?? 'exampleToken';
        $requestStack = $parameters['requestStack'] ?? $this->createMock(RequestStack::class);

        return new RoutingUtil($container, $router, $csrfTokenName, $requestStack);
    }

    public function testGenerateBackendRoute()
    {
        $router = $this->createMock(RouterInterface::class);
        $router->method('generate')->willReturnCallback(function (string $route, array $parameters = []) {
            if ('contao_backend' === $route) {
                $url = '/contao';
            } else {
                $url = '/'.$route;
            }

            if (!empty($parameters)) {
                $url .= '?'.http_build_query($parameters);
            }

            return $url;
        });

        $tokenManager = $this->createMock(ContaoCsrfTokenManager::class);
        $token = $this->createMock(CsrfToken::class);
        $token->method('getValue')->willReturn('foo-bar');
        $tokenManager->method('getToken')->willReturn($token);
        $container = $this->getContainerWithContaoConfiguration();
        $container->set(ContaoCsrfTokenManager::class, $tokenManager);
        $container->set(CsrfTokenManagerInterface::class, $tokenManager);

        $requestStack = new RequestStack();
        $request = new Request();
        $request->query->set('_contao_referer_id', 'win-amp');
        $requestStack->push($request);

        $instance = $this->getTestInstance([
            'router' => $router,
            'container' => $container,
            'requestStack' => $requestStack,
        ]);

        $this->assertSame('/contao', $instance->generateBackendRoute([], false, false));
        $this->assertSame('/contao?rt=foo-bar', $instance->generateBackendRoute([], true, false));
        $this->assertSame('/contao?rt=foo-bar&ref=win-amp', $instance->generateBackendRoute([], true, true));
        $this->assertSame('/contao?a=b&rt=foo-bar&ref=win-amp', $instance->generateBackendRoute(['a' => 'b'], true, true));

        $container = new ContainerBuilder();
        $container->set(CsrfTokenManagerInterface::class, $tokenManager);

        $instance = $this->getTestInstance([
            'router' => $router,
            'container' => $container,
            'requestStack' => $requestStack,
        ]);

        $this->assertSame('/contao?rt=foo-bar', $instance->generateBackendRoute([], true, false));
    }
}

<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class RoutingUtilTest extends AbstractUtilsTestCase
{
    public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null)
    {
        $router = $parameters['router'] ?? $this->createMock(RouterInterface::class);
        $csrfTokenName = $parameters['csrfTokenName'] ?? 'exampleToken';
        $requestStack = $parameters['requestStack'] ?? $this->createMock(RequestStack::class);
        $csrfTokenManager = $parameters['csrfTokenManager'] ?? $this->createMock(ContaoCsrfTokenManager::class);

        return new RoutingUtil($csrfTokenManager, $router, $csrfTokenName, $requestStack);
    }

    public function testGenerateBackendRoute()
    {
        $router = $this->createMock(RouterInterface::class);
        $router->method('generate')->willReturnCallback(function (string $route, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH) {
            if ('contao_backend' === $route) {
                $url = '/contao';
            } else {
                $url = '/'.$route;
            }

            if (!empty($parameters)) {
                $url .= '?'.http_build_query($parameters);
            }

            if (UrlGeneratorInterface::ABSOLUTE_URL === $referenceType) {
                $url = 'https://example.org'.$url;
            }

            return $url;
        });

        $tokenManager = $this->createMock(ContaoCsrfTokenManager::class);
        $token = $this->createMock(CsrfToken::class);
        $token->method('getValue')->willReturn('foo-bar');
        $tokenManager->method('getToken')->willReturn($token);

        $requestStack = new RequestStack();
        $request = new Request();
        $request->query->set('_contao_referer_id', 'win-amp');
        $requestStack->push($request);

        $instance = $this->getTestInstance([
            'router' => $router,
            'requestStack' => $requestStack,
            'csrfTokenManager' => $tokenManager,
        ]);

        $this->assertSame('/contao', $instance->generateBackendRoute([], false, false));
        $this->assertSame('/contao?rt=foo-bar', $instance->generateBackendRoute([], true, false));
        $this->assertSame('/contao?rt=foo-bar&ref=win-amp', $instance->generateBackendRoute([], true, true));
        $this->assertSame('/contao?a=b&rt=foo-bar&ref=win-amp', $instance->generateBackendRoute(['a' => 'b'], true, true));
        $this->assertSame(
            'https://example.org/contao',
            $instance->generateBackendRoute([], false, false, ['absoluteUrl' => true])
        );
        $this->assertSame(
            '/contao_internal',
            $instance->generateBackendRoute([], false, false, ['route' => 'contao_internal'])
        );
    }
}

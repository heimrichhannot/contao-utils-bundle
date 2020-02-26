<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Routing;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Routing\RoutingUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

class RoutingUtilTest extends ContaoTestCase
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function setUp()
    {
        $router = $this->createMock(RouterInterface::class);
        $router
            ->method('generate')
            ->with('contao_backend', $this->anything())
            ->willReturnCallback(function ($route, $params = []) {
                $url = '/contao';

                if (!empty($params)) {
                    $count = 0;

                    foreach ($params as $key => $value) {
                        $url .= (0 === $count ? '?' : '&');
                        $url .= $key.'='.$value;
                        ++$count;
                    }
                }

                return $url;
            })
        ;
        $requestStack = new RequestStack();
        $request = new Request();
        $request->attributes->set('_contao_referer_id', 'foobar');
        $requestStack->push($request);
        $tokenManager = $this->createMock(CsrfTokenManager::class);
        $tokenManager->method('getToken')->with('dummy_token')->willReturn(new CsrfToken('dummy_token', 'abcd'));

        $tokenGenerator = $this->createMock(CsrfTokenManager::class);
        $tokenGenerator->method('getToken')->with('dummy_token')->willReturn(new CsrfToken('dummy_token', 'abcd'));

        $this->container = $this->mockContainer();
        $this->container->set('router', $router);
        $this->container->set('request_stack', $requestStack);
        $this->container->setParameter('contao.csrf_token_name', 'dummy_token');
        $this->container->set('contao.csrf.token_manager', $tokenManager);
        $this->container->set('security.csrf.token_manager', $tokenManager);
        $this->container->set('security.csrf.token_generator', $tokenGenerator);

        System::setContainer($this->container);
    }

    public function testGenerateBackendRoute()
    {
        $router = new RoutingUtil(
            $this->container->get('router'),
            $this->container->get('request_stack'),
            $this->container->getParameter('contao.csrf_token_name')
        );
        $this->assertSame('/contao', $router->generateBackendRoute([], false, false));
        $this->assertSame('/contao?rt=abcd', $router->generateBackendRoute([], true, false));
        $this->assertSame('/contao?rt=abcd&ref=foobar', $router->generateBackendRoute([], true, true));
        $this->assertSame('/contao?ref=foobar', $router->generateBackendRoute([], false, true));
    }
}

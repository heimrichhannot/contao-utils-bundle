<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Routing;

use Contao\System;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class RoutingUtil
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var string
     */
    private $token;
    /**
     * @var RequestStack
     */
    private $request;

    public function __construct(RouterInterface $router, RequestStack $request, $token)
    {
        $this->router = $router;
        $this->token = $token;
        $this->request = $request;
    }

    /**
     * Generate a backend route with token and referer.
     *
     * @param array $params Url-Parameters
     *
     * @return string The backend route url
     */
    public function generateBackendRoute(array $params = [], bool $addToken = true, bool $addReferer = true, string $route = 'contao_backend')
    {
        if ($addToken) {
            // >= contao 4.6.8 uses contao.csrf.token_manager service to validate token
            if (System::getContainer()->has('contao.csrf.token_manager')) {
                $params['rt'] = System::getContainer()->get('contao.csrf.token_manager')->getToken($this->token)->getValue();
            } else {
                $params['rt'] = System::getContainer()->get('security.csrf.token_manager')->getToken($this->token)->getValue();
            }
        }

        if ($addReferer) {
            $params['ref'] = $this->request->getCurrentRequest()->get('_contao_referer_id');
        }

        return $this->router->generate($route, $params);
    }
}

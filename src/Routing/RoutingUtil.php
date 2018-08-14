<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Routing;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

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
     * @var CsrfTokenManager
     */
    private $tokenManager;
    private $token;
    /**
     * @var RequestStack
     */
    private $request;

    public function __construct(RouterInterface $router, RequestStack $request, CsrfTokenManager $tokenManager, $token)
    {
        $this->router = $router;
        $this->tokenManager = $tokenManager;
        $this->token = $token;
        $this->request = $request;
    }

    /**
     * Generate a backend route with token and referer.
     *
     * @param array $params     Url-Parameters
     * @param bool  $addToken
     * @param bool  $addReferer
     *
     * @return string The backend route url
     */
    public function generateBackendRoute(array $params = [], $addToken = true, $addReferer = true)
    {
        if ($addToken) {
            $params['rt'] = $this->tokenManager->getToken($this->token)->getValue();
        }
        if ($addReferer) {
            $params['ref'] = $this->request->getCurrentRequest()->get('_contao_referer_id');
        }

        return $this->router->generate('contao_backend', $params);
    }
}

<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util\Routing;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class RoutingUtil implements ServiceSubscriberInterface
{
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var string
     */
    private $csrfTokenName;
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(ContainerInterface $container, RouterInterface $router, string $csrfTokenName, RequestStack $requestStack)
    {
        $this->router = $router;
        $this->container = $container;
        $this->csrfTokenName = $csrfTokenName;
        $this->requestStack = $requestStack;
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
            if ($this->container->has('contao.csrf.token_manager')) {
                $params['rt'] = $this->container->get('contao.csrf.token_manager')->getToken($this->csrfTokenName)->getValue();
            } else {
                $params['rt'] = $this->container->get('security.csrf.token_manager')->getToken($this->csrfTokenName)->getValue();
            }
        }

        if ($addReferer && ($request = $this->requestStack->getCurrentRequest())) {
            $params['ref'] = $request->get('_contao_referer_id');
        }

        return $this->router->generate($route, $params);
    }

    public static function getSubscribedServices()
    {
        return [
            '?contao.csrf.token_manager',
            '?security.csrf.token_manager',
        ];
    }
}

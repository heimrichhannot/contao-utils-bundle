<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util\Routing;

use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use HeimrichHannot\UtilsBundle\Util\AbstractServiceSubscriber;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class RoutingUtil extends AbstractServiceSubscriber
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
    public function generateBackendRoute(array $params = [], bool $addToken = true, bool $addReferer = true, string $route = 'contao_backend'): string
    {
        if ($addToken) {
            // >= contao 4.6.8 uses contao.csrf.token_manager service to validate token
            if ($this->container->has(ContaoCsrfTokenManager::class)) {
                $params['rt'] = $this->container->get(ContaoCsrfTokenManager::class)->getToken($this->csrfTokenName)->getValue();
            } elseif ($this->container->has(CsrfTokenManagerInterface::class)) {
                $params['rt'] = $this->container->get(CsrfTokenManagerInterface::class)->getToken($this->csrfTokenName)->getValue();
            }
        }

        if ($addReferer && ($request = $this->requestStack->getCurrentRequest())) {
            $params['ref'] = $request->get('_contao_referer_id');
        }

        return $this->router->generate($route, $params);
    }

    /**
     * @codeCoverageIgnore
     */
    public static function getSubscribedServices()
    {
        return [
            '?'.ContaoCsrfTokenManager::class,
            '?'.CsrfTokenManagerInterface::class,
        ];
    }
}

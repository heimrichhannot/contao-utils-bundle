<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util\Routing;

use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use HeimrichHannot\UtilsBundle\Util\AbstractServiceSubscriber;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
     * Options:
     * - absoluteUrl (bool): Return absolute url (default: false)
     * - route (string): Override the default backend route (default: contao_backend)
     *
     * @param array $params Url-Parameters
     * @param bool $addToken
     * @param bool $addReferer
     * @param array{
     *     route: string,
     *     absoluteUrl: bool,
     * }|string $options
     * @return string The backend route url
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function generateBackendRoute(array $params = [], bool $addToken = true, bool $addReferer = true, $options = [], $route = []): string
    {
        if (is_string($options)) {
            trigger_deprecation(
                'heimrichhannot/contao-utils-bundle',
                '2.244.0',
                'Passing a string as fourth parameter is deprecated. Use an array with the key "route" instead.'
            );
            $options = ['route' => $options];
        } elseif (!is_array($options)) {
            throw new \InvalidArgumentException('Fourth parameter must be a string or an array.');
        }

        if (!empty($route)) {
            trigger_deprecation(
                'heimrichhannot/contao-utils-bundle',
                '2.244.0',
                'Passing more than four parameters or the route parameter is deprecated. Use an array as fourth parameter \'options\' with the key "route" instead.'
            );
            if (is_string($route)) {
                if (!isset($options['route'])) {
                    $options['route'] = $route;
                }
            } elseif (is_array($route)) {
                $options = array_merge($route, $options);
            } else {
                throw new \InvalidArgumentException('Parameter route must be a string or an array.');
            }
        }

        $options = array_merge(
            ['absoluteUrl' => false],
            $options
        );

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

        return $this->router->generate(
            $options['route'] ?? 'contao_backend',
            $params,
            $options['absoluteUrl'] ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH
        );
    }

    /**
     * @codeCoverageIgnore
     */
    public static function getSubscribedServices(): array
    {
        return [
            '?'.ContaoCsrfTokenManager::class,
            '?'.CsrfTokenManagerInterface::class,
        ];
    }
}

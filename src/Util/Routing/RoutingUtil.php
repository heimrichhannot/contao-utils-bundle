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

class RoutingUtil
{

    public function __construct(
        private ContaoCsrfTokenManager $tokenManager,
        private RouterInterface $router,
        private string $csrfTokenName,
        private RequestStack $requestStack
    )
    {
    }

    /**
     * Generate a backend route with token and referer.
     *
     * Options:
     * - absoluteUrl: Return absolute url (default: false)
     * - route: Route name (default: contao_backend)
     *
     * @param array $params Url-Parameters
     * @param array{
     *     absoluteUrl?: bool,
     *     route?: string
     * } $options Options
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @return string The backend route url
     */
    public function generateBackendRoute(array $params = [], bool $addToken = true, bool $addReferer = true, array $options = []): string
    {
        $options = array_merge([
            'absoluteUrl' => false,
            'route' => 'contao_backend',
        ], $options
        );

        if ($addToken) {
            $params['rt'] = $this->tokenManager->getToken($this->csrfTokenName)->getValue();
        }

        if ($addReferer && ($request = $this->requestStack->getCurrentRequest())) {
            $params['ref'] = $request->get('_contao_referer_id');
        }

        return $this->router->generate(
            $options['route'],
            $params,
            $options['absoluteUrl'] ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH
        );
    }
}

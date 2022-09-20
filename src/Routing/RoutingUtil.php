<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Routing;

use HeimrichHannot\UtilsBundle\Util\Utils;

/**
 * @deprecated Use Utils service instead
 * @codeCoverageIgnore
 */
class RoutingUtil
{
    /**
     * @var Utils
     */
    private $utils;

    public function __construct(Utils $utils)
    {
        $this->utils = $utils;
    }

    /**
     * Generate a backend route with token and referer.
     *
     * @param array $params Url-Parameters
     *
     * @return string The backend route url
     *
     * @deprecated Use utils service instead
     * @codeCoverageIgnore
     */
    public function generateBackendRoute(array $params = [], bool $addToken = true, bool $addReferer = true, string $route = 'contao_backend')
    {
        return $this->utils->routing()->generateBackendRoute($params, $addToken, $addReferer, $route);
    }
}

<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util\Request;

use HeimrichHannot\UtilsBundle\Exception\InvalidUrlException;
use Symfony\Component\HttpFoundation\RequestStack;

class UrlUtil
{
    /** @var RequestStack */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Remove a query parameter (GET parameter) from an url.
     * If no url is given, the method tries to get the current url from the request.
     *
     * @param string $parameter The query parameter name to remove
     * @param string $url       the url where the query parameter should be removed
     */
    public function removeQueryStringParameterToUrl(string $parameter, string $url = ''): string
    {
        if (empty($url)) {
            $request = $this->requestStack->getCurrentRequest();

            if (!$request) {
                return '';
            }
            $url = $request->getUri();
        }

        $parsedUrl = parse_url($url);
        $query = [];

        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $query);
            unset($query[$parameter]);
        }

        $parsedUrl['query'] = !empty($query) ? http_build_query($query) : '';

        return $this->buildUrlString($parsedUrl);
    }

    /**
     * Add a query string parameter to an url.
     * If no url is given, the current request url is used.
     */
    public function addQueryStringParameterToUrl(string $parameter, string $url = ''): string
    {
        if (empty($url)) {
            $request = $this->requestStack->getCurrentRequest();

            if (!$request) {
                return '';
            }
            $url = $request->getUri();
        }

        $parsedUrl = parse_url($url);

        $pairs = [];

        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $pairs);
        }

        parse_str(str_replace('&amp;', '&', $parameter), $newPairs);
        $pairs = array_merge($pairs, $newPairs);

        $parsedUrl['query'] = (!empty($pairs) ? http_build_query($pairs) : '');

        return $this->buildUrlString($parsedUrl);
    }

    /**
     * Convert an absolute url to a relative url.
     *
     * Options:
     * - removeLeadingSlash: (boolean) Remove leading slash from path
     *
     * @param string $url     The url that should be made relative
     * @param array  $options Pass additional options
     *
     * @throws InvalidUrlException
     */
    public function makeUrlRelative(string $url, array $options = []): string
    {
        $options = array_merge([
            'removeLeadingSlash' => false,
        ], $options);

        $urlParts = parse_url($url);

        if (false === $urlParts) {
            throw new InvalidUrlException('Your given url is invalid and could not be parsed.');
        }

        $path = '';

        if (isset($urlParts['path'])) {
            $path .= $urlParts['path'];

            if ($options['removeLeadingSlash']) {
                $path = ltrim($path, '/');
            }
        }

        if (isset($urlParts['query'])) {
            $path .= '?'.$urlParts['query'];
        }

        if (isset($urlParts['fragment'])) {
            $path .= '#'.$urlParts['fragment'];
        }

        return $path;
    }

    private function buildUrlString(array $parsedUrl): string
    {
        return
            ((isset($parsedUrl['scheme']) && isset($parsedUrl['host'])) ? $parsedUrl['scheme'].'://' : '').
            ($parsedUrl['host'] ?? '').
            ($parsedUrl['path'] ?? '').
            (isset($parsedUrl['query']) ? '?'.$parsedUrl['query'] : '').
            (isset($parsedUrl['fragment']) ? '#'.$parsedUrl['fragment'] : '')
        ;
    }
}

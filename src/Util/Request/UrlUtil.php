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
    public function removeQueryStringParameterFromUrl(string $parameter, string $url = ''): string
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
     * @deprecated Use removeQueryStringParameterFromUrl() instead
     * @codeCoverageIgnore
     */
    public function removeQueryStringParameterToUrl(string $parameter, string $url = ''): string
    {
        return $this->removeQueryStringParameterFromUrl($parameter, $url);
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

        $newPairs = [];
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

        /** @var array|false $urlParts */
        $urlParts = parse_url($url);

        if (false === $urlParts) {
            throw new InvalidUrlException('Your given url is invalid and could not be parsed.');
        }

        unset($urlParts['schema'], $urlParts['host'], $urlParts['port'], $urlParts['user'], $urlParts['pass']);

        if (isset($urlParts['path']) && $options['removeLeadingSlash']) {
            $urlParts['path'] = ltrim($urlParts['path'], '/');
        }

        return $this->buildUrlString($urlParts);
    }

    private function buildUrlString(array $parsedUrl): string
    {
        return
            ((!empty($parsedUrl['scheme']) && !empty($parsedUrl['host'])) ? $parsedUrl['scheme'].'://' : '').
            ($parsedUrl['host'] ?? '').
            (!empty($parsedUrl['path']) ? (!empty($parsedUrl['host']) ? '/'.ltrim($parsedUrl['path'], '/') : $parsedUrl['path']) : '').
            (!empty($parsedUrl['query']) ? '?'.$parsedUrl['query'] : '').
            (!empty($parsedUrl['fragment']) ? '#'.$parsedUrl['fragment'] : '')
        ;
    }
}

<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util;

use HeimrichHannot\UtilsBundle\Exception\InvalidUrlException;
use Symfony\Component\HttpFoundation\RequestStack;

class UrlUtil
{
    public function __construct(private RequestStack $requestStack) {}

    /**
     * Remove query parameters (GET parameter) from a URL.
     * You can pass a string or an associative array to $parameter.
     * If no URL is given, the current request url is used.
     *
     * @example removeQueryStringParameterFromUrl('foo', 'https://example.com?foo=bar&baz=fuzz') // https://example.com?baz=fuzz
     * @example removeQueryStringParameterFromUrl(['foo', 'baz'], 'https://example.com?foo=bar&baz=fuzz') // https://example.com
     *
     * @param string|array<string> $parameter The query parameter names to remove.
     * @param string               $url       The URL to rid of the given query parameters.
     *
     * @return string The URL without the given query parameters.
     */
    public function removeQueryStringParameterFromUrl(string|array $parameter, string $url = ''): string
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

        if (isset($parsedUrl['query']))
        {
            parse_str($parsedUrl['query'], $query);

            if (is_string($parameter))
            {
                unset($query[$parameter]);
            }
            else foreach ($parameter as $param)
            {
                unset($query[$param]);
            }
        }

        $parsedUrl['query'] = !empty($query) ? http_build_query($query) : '';

        return $this->buildUrlString($parsedUrl);
    }

    /**
     * Add a query string parameter to a URL.
     * You can pass a string or an associative array to $parameter.
     * If no url is given, the current request URL is used.
     *
     * @example addQueryStringParameterToUrl('foo=bar', 'https://example.com') // https://example.com?foo=bar
     * @example addQueryStringParameterToUrl(['foo' => 'bar'], 'https://example.com') // https://example.com?foo=bar
     *
     * @param string|array<string, string> $parameter The query parameters to add.
     * @param string                       $url       The URL to which query parameter should be added.
     *
     * @return string The concatenated URL.
     */
    public function addQueryStringParameterToUrl(string|array $parameter, string $url = ''): string
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

        if (is_string($parameter)) {
            $newPairs = [];
            parse_str(str_replace('&amp;', '&', $parameter), $newPairs);
        } else {
            $newPairs = $parameter;
        }

        $pairs = array_merge($pairs, $newPairs);

        $parsedUrl['query'] = (!empty($pairs) ? http_build_query($pairs) : '');

        return $this->buildUrlString($parsedUrl);
    }

    /**
     * Convert an absolute url to a relative url.
     *
     * Options:
     * - removeLeadingSlash: Remove leading slash from path
     *
     * @param string $url     The url that should be made relative
     * @param array{
     *     removeLeadingSlash?: bool
     * }  $options Pass additional options
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

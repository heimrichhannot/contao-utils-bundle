<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Url;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Environment;
use Contao\Model;
use Contao\PageModel;
use Contao\System;
use HeimrichHannot\UtilsBundle\Exception\InvalidUrlException;
use HeimrichHannot\UtilsBundle\Request\RequestUtil;

class UrlUtil
{
    const TERMINATE_HEADERS_ALREADY_SENT = 800;
    const TERMINATE_EXIT_LOCATION_SET = 900;

    /** @var ContaoFrameworkInterface */
    protected $framework;
    /**
     * @var RequestUtil
     */
    private $requestUtil;

    public function __construct(ContaoFrameworkInterface $framework, RequestUtil $requestUtil)
    {
        $this->framework = $framework;
        $this->requestUtil = $requestUtil;
    }

    /**
     * Detect if user already visited our domain before.
     *
     * @deprecated please use RequestUtil::isNewVisitor() instead
     * @codeCoverageIgnore
     */
    public function isNewVisitor(): bool
    {
        @trigger_error(__METHOD__.' is deprecated and will be removed in a future version. Please use RequestUtil::isNewVisitor() instead.', E_USER_DEPRECATED);

        return $this->requestUtil->isNewVisitor();
    }

    /**
     * Return the current url with requestUri.
     *
     * Options:
     *
     * * skipParams: boolean
     *
     * @return string
     */
    public function getCurrentUrl(array $options)
    {
        $url = Environment::get('url');

        if (isset($options['skipParams']) && $options['skipParams']) {
            $url .= parse_url(Environment::get('uri'), PHP_URL_PATH);
        } else {
            $url .= Environment::get('requestUri');
        }

        return $url;
    }

    /**
     * Add a query string to the given URI string or page ID.
     *
     * @param string $query
     * @param mixed  $url
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function addQueryString($query, $url = null)
    {
        $queryString = '';
        $url = static::prepareUrl($url);
        $query = trim(ampersand($query, false), '&');

        $explodedUrl = explode('?', $url, 2);

        if (2 === \count($explodedUrl)) {
            [$script, $queryString] = $explodedUrl;
        } else {
            [$script] = $explodedUrl;
        }

        parse_str($queryString, $queries);

        $queries = array_filter($queries);
        unset($queries['language']);

        $href = '';

        if (!empty($queries)) {
            parse_str($query, $new);
            $href = '?'.http_build_query(array_merge($queries, $new), '', '&');
        } elseif (!empty($query)) {
            $href = '?'.$query;
        }

        return $script.$href;
    }

    /**
     * Remove query parameters from the current URL.
     *
     * Options:
     * - absoluteUrl: (boolean) Return absolute url instead of relative url. Only applicable if id or null is given as url. Default: false
     *
     * @param array           $params List of parameters to remove from url
     * @param string|int|null $url    Full Uri, Page id or null (for current environment uri)
     *
     * @return string
     */
    public function removeQueryString(array $params, $url = null, array $options = [])
    {
        $strUrl = static::prepareUrl($url, $options);

        if (empty($params)) {
            return $strUrl;
        }

        $explodedUrl = explode('?', $strUrl, 2);

        if (2 === \count($explodedUrl)) {
            [$script, $queryString] = $explodedUrl;
        } else {
            [$script] = $explodedUrl;

            return $script;
        }

        parse_str($queryString, $queries);

        $queries = array_filter($queries);
        $queries = array_diff_key($queries, array_flip($params));

        $href = '';

        if (!empty($queries)) {
            $href .= '?'.http_build_query($queries);
        }

        return $script.$href;
    }

    /**
     * @return PageModel|null
     */
    public function getJumpToPageObject(int $jumpTo, bool $fallbackToObjPage = true)
    {
        global $objPage;

        if ($jumpTo && $jumpTo != $objPage->id
            && null !== ($jumpToPage = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_page', $jumpTo))) {
            return $jumpToPage;
        }

        return $fallbackToObjPage ? $objPage : null;
    }

    public function getJumpToPageUrl(int $jumpTo, bool $fallbackToObjPage = true): ?string
    {
        $jumpToObject = $this->getJumpToPageObject($jumpTo, $fallbackToObjPage);

        if (null === $jumpToObject || !($jumpToObject instanceof Model)) {
            return null;
        }

        return $jumpToObject->getFrontendUrl();
    }

    public static function addAutoItemToPage(Model $page, Model $entity, $autoItemType = 'items')
    {
        $autoItem = ((\Config::get('useAutoItem') && !\Config::get('disableAlias')) ? '/' : '/'.$autoItemType.'/').((!\Config::get('disableAlias') && '' != $entity->alias) ? $entity->alias : $entity->id);

        return \Controller::generateFrontendUrl($page->row(), $autoItem);
    }

    /**
     * Redirect to another page.
     *
     * @param string $strLocation The target URL
     * @param int    $intStatus   The HTTP status code (defaults to 303)
     * @param bool   $test        For test purposes set to true to test exit/headers
     * @param bool   $skipSent    Skip if headers already sent for test purposes
     *
     * @return int|array|null
     */
    public function redirect($strLocation, $intStatus = 303, $test = false, $skipSent = false)
    {
        $headers = [];

        if (headers_sent() && !$skipSent) {
            if ($test) {
                return static::TERMINATE_HEADERS_ALREADY_SENT;
            }

            // @codeCoverageIgnoreStart
            exit;
            // @codeCoverageIgnoreEnd
        }

        $strLocation = str_replace('&amp;', '&', $strLocation);

        // Make the location an absolute URL
        if (!preg_match('@^https?://@i', $strLocation)) {
            $strLocation = \Environment::get('base').ltrim($strLocation, '/');
        }

        // Ajax request
        if (System::getContainer()->get('huh.request')->isXmlHttpRequest()) {
            $headers[] = 'HTTP/1.1 204 No Content';
            $headers[] = 'X-Ajax-Location: '.$strLocation;
        } else {
            // Add the HTTP header
            switch ($intStatus) {
                case 301:
                    $headers[] = 'HTTP/1.1 301 Moved Permanently';

                    break;

                case 302:
                    $headers[] = 'HTTP/1.1 302 Found';

                    break;

                case 303:
                    $headers[] = 'HTTP/1.1 303 See Other';

                    break;

                case 307:
                    $headers[] = 'HTTP/1.1 307 Temporary Redirect';

                    break;
            }

            $headers[] = 'Location: '.$strLocation;
        }

        if ($test) {
            return $headers;
        }

        // @codeCoverageIgnoreStart
        foreach ($headers as $header) {
            header($header);
        }

        exit;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Add a url scheme to a given url.
     */
    public function addURIScheme(string $url = '', string $protocol = 'http'): string
    {
        $scheme = $protocol.'://';

        if ('' !== $url && false === System::getContainer()->get('huh.utils.string')->startsWith($url, $protocol)) {
            $url = $scheme.$url;
        }

        return $url;
    }

    /**
     * Prepare URL from ID and keep query string from current string.
     *
     * Options:
     * - absoluteUrl: (boolean) Return absolute url instead of relative url. Only applicable if id or null is given as url. Default: false
     *
     * @param string|int|null Url or page id
     * @param array $options pass additional options
     *
     * @return string
     */
    public function prepareUrl($url = null, array $options = [])
    {
        if (null === $url) {
            if (isset($options['absoluteUrl']) && true === $options['absoluteUrl']) {
                $url = Environment::get('uri');
            } else {
                $url = Environment::get('requestUri');
            }
        } elseif (is_numeric($url)) {
            /** @var PageModel $jumpTo */
            if (null === ($jumpTo = $this->framework->getAdapter(PageModel::class)->findByPk($url))) {
                throw new \InvalidArgumentException('Given page id does not exist.');
            }

            if (isset($options['absoluteUrl']) && true === $options['absoluteUrl']) {
                $url = $jumpTo->getAbsoluteUrl();
            } else {
                $url = $jumpTo->getFrontendUrl();
            }

            [, $queryString] = explode('?', Environment::get('request'), 2);

            if ('' != $queryString) {
                $url .= '?'.$queryString;
            }
        }

        $url = ampersand($url, false);

        return $url;
    }

    /**
     * Convert an absolute url to an relative url.
     *
     * Options:
     * - removeLeadingSlash: (boolean) Remove a
     *
     * @param string $url     The url that should be made relative
     * @param array  $options Pass additional options
     *
     * @throws InvalidUrlException
     */
    public function getRelativePath(string $url, array $options = []): string
    {
        $urlParts = parse_url($url);

        if (false === $urlParts) {
            throw new InvalidUrlException('Your given url is invalid and could not be parsed.');
        }

        $path = '';

        if (isset($urlParts['path'])) {
            $path .= $urlParts['path'];

            if (isset($options['removeLeadingSlash']) && true === $options['removeLeadingSlash']) {
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

    public function getBaseUrl(bool $absolute = false)
    {
        return ($absolute ? Environment::get('host') : '').(System::getContainer()->get('huh.utils.container')->isDev() ? '/app_dev.php' : '');
    }
}

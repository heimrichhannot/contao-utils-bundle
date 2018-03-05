<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Url;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Environment;
use Contao\PageModel;
use Contao\System;

class UrlUtil
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    public function getCurrentUrl(array $options)
    {
        $url = Environment::get('url').Environment::get('requestUri');

        if (isset($options['skipParams']) && $options['skipParams']) {
            $url = Environment::get('url').parse_url(Environment::get('uri'), PHP_URL_PATH);
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

        if (2 === count($explodedUrl)) {
            list($script, $queryString) = $explodedUrl;
        } else {
            list($script) = $explodedUrl;
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
     * @param array           $params
     * @param string|int|null $url
     *
     * @return string
     */
    public function removeQueryString(array $params, $url = null)
    {
        $strUrl = static::prepareUrl($url);

        if (empty($params)) {
            return $strUrl;
        }

        $explodedUrl = explode('?', $strUrl, 2);

        if (2 === count($explodedUrl)) {
            list($script, $queryString) = $explodedUrl;
        } else {
            list($script) = $explodedUrl;

            return $script;
        }

        parse_str($queryString, $queries);

        $queries = array_filter($queries);
        $queries = array_diff_key($queries, array_flip($params));

        $href = '';

        if (!empty($queries)) {
            $href .= '?'.http_build_query($queries, '', '&');
        }

        return $script.$href;
    }

    /**
     * @param int  $jumpTo
     * @param bool $fallbackToObjPage
     *
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

    /**
     * Redirect to another page.
     *
     * @param string $strLocation The target URL
     * @param int    $intStatus   The HTTP status code (defaults to 303)
     */
    public function redirect($strLocation, $intStatus = 303)
    {
        if (headers_sent()) {
            exit;
        }

        $strLocation = str_replace('&amp;', '&', $strLocation);

        // Make the location an absolute URL
        if (!preg_match('@^https?://@i', $strLocation)) {
            $strLocation = \Environment::get('base').ltrim($strLocation, '/');
        }

        // Ajax request
        if (\Environment::get('isAjaxRequest')) {
            header('HTTP/1.1 204 No Content');
            header('X-Ajax-Location: '.$strLocation);
        } else {
            // Add the HTTP header
            switch ($intStatus) {
                case 301:
                    header('HTTP/1.1 301 Moved Permanently');
                    break;

                case 302:
                    header('HTTP/1.1 302 Found');
                    break;

                case 303:
                    header('HTTP/1.1 303 See Other');
                    break;

                case 307:
                    header('HTTP/1.1 307 Temporary Redirect');
                    break;
            }

            header('Location: '.$strLocation);
        }

        exit;
    }

    /**
     * Prepare URL from ID and keep query string from current string.
     *
     * @param string|int|null
     *
     * @return string
     */
    protected function prepareUrl($url)
    {
        if (null === $url) {
            $url = Environment::get('requestUri');
        } elseif (is_numeric($url)) {
            if (null === ($jumpTo = $this->framework->getAdapter(PageModel::class)->findByPk($url))) {
                throw new \InvalidArgumentException('Given page id does not exist.');
            }

            $url = $this->framework->getAdapter(Controller::class)->generateFrontendUrl($jumpTo->row());

            list(, $queryString) = explode('?', Environment::get('request'), 2);

            if ('' != $queryString) {
                $url .= '?'.$queryString;
            }
        }

        $url = ampersand($url, false);

        return $url;
    }
}

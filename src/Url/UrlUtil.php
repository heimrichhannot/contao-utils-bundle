<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\UtilsBundle\Url;

use Contao\Controller;
use Contao\Environment;
use Contao\PageModel;

class UrlUtil
{
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
    public static function addQueryString($query, $url = null)
    {
        $url = static::prepareUrl($url);
        $query = trim(ampersand($query, false), '&');

        list($script, $queryString) = explode('?', $url, 2);

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
    public static function removeQueryString(array $params, $url = null)
    {
        $strUrl = static::prepareUrl($url);

        if (empty($params)) {
            return $strUrl;
        }

        list($script, $queryString) = explode('?', $strUrl, 2);

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
     * Prepare URL from ID and keep query string from current string.
     *
     * @param string|int|null
     *
     * @return string
     */
    protected static function prepareUrl($url)
    {
        if (null === $url) {
            $url = Environment::get('request');
        } elseif (is_numeric($url)) {
            if (null === ($jumpTo = PageModel::findByPk($url))) {
                throw new \InvalidArgumentException('Given page id does not exist.');
            }

            $url = Controller::generateFrontendUrl($jumpTo->row());

            list(, $queryString) = explode('?', Environment::get('request'), 2);

            if ('' != $queryString) {
                $url .= '?'.$queryString;
            }
        }

        $url = ampersand($url, false);

        return $url;
    }
}

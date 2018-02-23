<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Request;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CurlRequestUtil
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var HttpRequestInterface
     */
    protected $handle = null;

    public function __construct(ContaoFrameworkInterface $framework, ContainerInterface $container)
    {
        $this->framework = $framework;
        $this->container = $container;
    }

    /**
     * Executes a curl request while taking
     *
     * @param $url
     * @param array $requestHeaders
     * @param bool $returnResponseHeaders
     *
     * @return array|mixed
     */
    public function request(string $url, array $requestHeaders = [], $returnResponseHeaders = false)
    {
        $handle = $this->createCurlHandle($url);

        if ($proxy = Config::get('hpProxy'))
        {
            $handle->setOption(CURLOPT_PROXY, $proxy);
        }

        if (!empty($requestHeaders))
        {
            static::setHeaders($handle, $requestHeaders);
        }

        if ($returnResponseHeaders)
        {
            $handle->setOption(CURLOPT_HEADER, true);
        }

        $response   = $handle->execute();
        $statusCode = $handle->getInfo(CURLINFO_HTTP_CODE);
        $handle->close();

        if ($returnResponseHeaders)
        {
            return static::splitResponseHeaderAndBody($response, $statusCode);
        }

        return $response;
    }

    /**
     * Recursivly send get request and terminates if termination condition is given or max request count is reached.
     *
     * @param int $maxRecursionCount
     * @param callable $callback Termination condition callback. Return true to terminate.
     * @param string $url
     * @param array $requestHeaders
     * @param bool $returnResponseHeaders
     *
     * @return array|mixed|null
     */
    public function recursiveGetRequest(int $maxRecursionCount, callable $callback, string $url, array $requestHeaders = [], bool $returnResponseHeaders = false)
    {
        $i            = 0;
        $terminate = false;
        $result    = null;

        while ($i++ < $maxRecursionCount && !$terminate)
        {
            $result = $this->request($url, $requestHeaders, $returnResponseHeaders);

            $terminate = $callback($result, $url, $requestHeaders, $returnResponseHeaders, $maxRecursionCount);
        }

        return $result;
    }

    /**
     * Recursivly send post request and terminates if termination condition is given or max request count is reached.
     *
     * @param int $maxRecursionCount
     * @param callable $callback
     * @param string $url
     * @param array $requestHeaders
     * @param array $post
     * @param bool $returnResponseHeaders
     * @return array|mixed|null
     */
    public function recursivePostRequest(int $maxRecursionCount, callable $callback, string $url, array $requestHeaders = [], array $post = [], bool $returnResponseHeaders = false)
    {
        $i            = 0;
        $terminate = false;
        $result    = null;

        while ($i++ < $maxRecursionCount && !$terminate)
        {
            $result = $this->postRequest($url, $requestHeaders, $post, $returnResponseHeaders);

            $terminate = $callback($result, $url, $requestHeaders, $post, $returnResponseHeaders, $maxRecursionCount);
        }

        return $result;
    }

    public function postRequest($strUrl, array $arrRequestHeaders = [], array $arrPost = [], $blnReturnResponseHeaders = false)
    {
        $objCurl = static::createCurlHandle($strUrl);

        if (Config::get('hpProxy'))
        {
            curl_setopt($objCurl, CURLOPT_PROXY, Config::get('hpProxy'));
        }

        if ($blnReturnResponseHeaders)
        {
            curl_setopt($objCurl, CURLOPT_HEADER, true);
        }

        if (!empty($arrRequestHeaders))
        {
            static::setHeaders($objCurl, $arrRequestHeaders);
        }

        if (!empty($arrPost))
        {
            curl_setopt($objCurl, CURLOPT_POST, true);
            curl_setopt($objCurl, CURLOPT_POSTFIELDS, http_build_query($arrPost));
        }

        $strResponse   = curl_exec($objCurl);
        $intStatusCode = curl_getinfo($objCurl, CURLINFO_HTTP_CODE);
        curl_close($objCurl);

        if ($blnReturnResponseHeaders)
        {
            return static::splitResponseHeaderAndBody($strResponse, $intStatusCode);
        }

        return $strResponse;
    }

    public function createCurlHandle($url)
    {
        $handle = $this->handle ?: new CurlRequest();
        $handle->init($url);
        $handle->setOption(CURLOPT_RETURNTRANSFER, true);
        $handle->setOption(CURLOPT_TIMEOUT, 10);
        return $handle;
    }

    public static function setHeaders($objCurl, array $arrHeaders)
    {
        $arrPrepared = [];

        foreach ($arrHeaders as $strName => $varValue)
        {
            $arrPrepared[] = $strName . ': ' . $varValue;
        }

        curl_setopt($objCurl, CURLOPT_HTTPHEADER, $arrPrepared);
    }

    public static function splitResponseHeaderAndBody($strResponse, $intStatusCode)
    {
        $arrHeaders = [];

        $intSplit  = strpos($strResponse, "\r\n\r\n");
        $strHeader = substr($strResponse, 0, $intSplit);
        $strBody   = str_replace($strHeader . "\r\n\r\n", '', $strResponse);

        foreach (explode("\r\n", $strHeader) as $i => $strLine)
        {
            if (0 === $i)
            {
                $arrHeaders['http_code'] = $intStatusCode;
            } else
            {
                list($strKey, $varValue) = explode(': ', $strLine);
                $arrHeaders[$strKey] = $varValue;
            }
        }

        return [$arrHeaders, trim($strBody)];
    }

    /**
     * Creates a linebreak separated list of the headers in $arrHeaders -> see request() and postRequest().
     *
     * @param array $arrHeaders
     *
     * @return string
     */
    public static function prepareHeaderArrayForPrint(array $arrHeaders)
    {
        $strResult = '';
        $i         = 0;

        foreach ($arrHeaders as $strKey => $strValue)
        {
            $strResult .= "$strKey: $strValue";

            if ($i++ != count($arrHeaders) - 1)
            {
                $strResult .= PHP_EOL;
            }
        }

        return $strResult;
    }
}

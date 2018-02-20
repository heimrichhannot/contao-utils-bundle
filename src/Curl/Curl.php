<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Curl;

use Contao\Config;

class Curl
{
    public static function recursivelyGetRequest($intMaxRecursionCount, $funcTerminationCondition, $strUrl, array $arrRequestHeaders = [], $blnReturnResponseHeaders = false)
    {
        $i = 0;
        $blnTerminate = false;
        $varResult = null;

        while ($i++ < $intMaxRecursionCount && !$blnTerminate) {
            $varResult = static::request($strUrl, $arrRequestHeaders, $blnReturnResponseHeaders);

            $blnTerminate = $funcTerminationCondition($varResult, $strUrl, $arrRequestHeaders, $blnReturnResponseHeaders, $intMaxRecursionCount);
        }

        return $varResult;
    }

    public function request($strUrl, array $arrRequestHeaders = [], $blnReturnResponseHeaders = false)
    {
        $objCurl = static::createCurlObject($strUrl);

        if (Config::get('hpProxy')) {
            curl_setopt($objCurl, CURLOPT_PROXY, Config::get('hpProxy'));
        }

        if (!empty($arrRequestHeaders)) {
            static::setHeaders($objCurl, $arrRequestHeaders);
        }

        if ($blnReturnResponseHeaders) {
            curl_setopt($objCurl, CURLOPT_HEADER, true);
        }

        $strResponse = curl_exec($objCurl);
        $intStatusCode = curl_getinfo($objCurl, CURLINFO_HTTP_CODE);
        curl_close($objCurl);

        if ($blnReturnResponseHeaders) {
            return static::splitResponseHeaderAndBody($strResponse, $intStatusCode);
        }

        return $strResponse;
    }

    public static function recursivelyPostRequest($intMaxRecursionCount, $funcTerminationCondition, $strUrl, array $arrRequestHeaders = [], array $arrPost = [], $blnReturnResponseHeaders = false)
    {
        $i = 0;
        $blnTerminate = false;
        $varResult = null;

        while ($i++ < $intMaxRecursionCount && !$blnTerminate) {
            $varResult = static::postRequest($strUrl, $arrRequestHeaders, $arrPost, $blnReturnResponseHeaders);

            $blnTerminate = $funcTerminationCondition($varResult, $strUrl, $arrRequestHeaders, $arrPost, $blnReturnResponseHeaders, $intMaxRecursionCount);
        }

        return $varResult;
    }

    public static function postRequest($strUrl, array $arrRequestHeaders = [], array $arrPost = [], $blnReturnResponseHeaders = false)
    {
        $objCurl = static::createCurlObject($strUrl);

        if (Config::get('hpProxy')) {
            curl_setopt($objCurl, CURLOPT_PROXY, Config::get('hpProxy'));
        }

        if ($blnReturnResponseHeaders) {
            curl_setopt($objCurl, CURLOPT_HEADER, true);
        }

        if (!empty($arrRequestHeaders)) {
            static::setHeaders($objCurl, $arrRequestHeaders);
        }

        if (!empty($arrPost)) {
            curl_setopt($objCurl, CURLOPT_POST, true);
            curl_setopt($objCurl, CURLOPT_POSTFIELDS, http_build_query($arrPost));
        }

        $strResponse = curl_exec($objCurl);
        $intStatusCode = curl_getinfo($objCurl, CURLINFO_HTTP_CODE);
        curl_close($objCurl);

        if ($blnReturnResponseHeaders) {
            return static::splitResponseHeaderAndBody($strResponse, $intStatusCode);
        }

        return $strResponse;
    }

    public static function createCurlObject($strUrl)
    {
        $objCurl = curl_init($strUrl);

        curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($objCurl, CURLOPT_TIMEOUT, 10);

        return $objCurl;
    }

    public static function setHeaders($objCurl, array $arrHeaders)
    {
        $arrPrepared = [];

        foreach ($arrHeaders as $strName => $varValue) {
            $arrPrepared[] = $strName.': '.$varValue;
        }

        curl_setopt($objCurl, CURLOPT_HTTPHEADER, $arrPrepared);
    }

    public static function splitResponseHeaderAndBody($strResponse, $intStatusCode)
    {
        $arrHeaders = [];

        $intSplit = strpos($strResponse, "\r\n\r\n");
        $strHeader = substr($strResponse, 0, $intSplit);
        $strBody = str_replace($strHeader."\r\n\r\n", '', $strResponse);

        foreach (explode("\r\n", $strHeader) as $i => $strLine) {
            if (0 === $i) {
                $arrHeaders['http_code'] = $intStatusCode;
            } else {
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
        $i = 0;

        foreach ($arrHeaders as $strKey => $strValue) {
            $strResult .= "$strKey: $strValue";

            if ($i++ != count($arrHeaders) - 1) {
                $strResult .= PHP_EOL;
            }
        }

        return $strResult;
    }
}

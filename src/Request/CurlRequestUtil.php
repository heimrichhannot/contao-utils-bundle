<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Request;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CurlRequestUtil
{
    const HTTP_STATUS_CODE_MESSAGES = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    ];

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
     * Executes a curl request while taking.
     *
     * @param $url
     * @param bool $returnResponseHeaders
     *
     * @return array|mixed
     */
    public function request(string $url, array $requestHeaders = [], $returnResponseHeaders = false)
    {
        $handle = $this->createCurlHandle($url);

        if ($proxy = Config::get('hpProxy')) {
            $handle->setOption(CURLOPT_PROXY, $proxy);
        }

        if (!empty($requestHeaders)) {
            $handle->setOption(CURLOPT_HTTPHEADER, $this->prepareHeaders($requestHeaders));
        }

        if ($returnResponseHeaders) {
            $handle->setOption(CURLOPT_HEADER, true);
        }

        $response = $handle->execute();
        $statusCode = $handle->getInfo(CURLINFO_HTTP_CODE);
        $handle->close();

        if ($response && $returnResponseHeaders) {
            return $this->splitResponseHeaderAndBody($response, $statusCode);
        }

        return $response;
    }

    /**
     * Create a curl post request.
     *
     * @return array|mixed
     */
    public function postRequest(string $url, array $requestHeaders = [], array $postFields = [], bool $returnResponseHeaders = false)
    {
        $handle = $this->createCurlHandle($url);

        if (Config::get('hpProxy')) {
            $handle->setOption(CURLOPT_PROXY, Config::get('hpProxy'));
        }

        if ($returnResponseHeaders) {
            $handle->setOption(CURLOPT_HEADER, true);
        }

        if (!empty($requestHeaders)) {
            $handle->setOption(CURLOPT_HTTPHEADER, $this->prepareHeaders($requestHeaders));
        }

        if (!empty($postFields)) {
            $handle->setOption(CURLOPT_POST, true);
            $handle->setOption(CURLOPT_POSTFIELDS, http_build_query($postFields));
        }

        $response = $handle->execute();

        $statusCode = $handle->getInfo(CURLINFO_HTTP_CODE);
        $handle->close();

        if ($response && $returnResponseHeaders) {
            return $this->splitResponseHeaderAndBody($response, $statusCode);
        }

        return $response;
    }

    /**
     * Recursivly send get request and terminates if termination condition is given or max request count is reached.
     *
     * @param callable $callback Termination condition callback. Return true to terminate.
     *
     * @return array|mixed|null
     */
    public function recursiveGetRequest(int $maxRecursionCount, callable $callback, string $url, array $requestHeaders = [], bool $returnResponseHeaders = false)
    {
        $i = 0;
        $terminate = false;
        $result = null;

        while ($i++ < $maxRecursionCount && !$terminate) {
            $result = $this->request($url, $requestHeaders, $returnResponseHeaders);

            $terminate = $callback($result, $url, $requestHeaders, $returnResponseHeaders, $maxRecursionCount, $i);
        }

        return $result;
    }

    /**
     * Recursivly send post request and terminates if termination condition is given or max request count is reached.
     *
     * @return array|mixed|null
     */
    public function recursivePostRequest(int $maxRecursionCount, callable $callback, string $url, array $requestHeaders = [], array $post = [], bool $returnResponseHeaders = false)
    {
        $i = 0;
        $terminate = false;
        $result = null;

        while ($i++ < $maxRecursionCount && !$terminate) {
            $result = $this->postRequest($url, $requestHeaders, $post, $returnResponseHeaders);

            $terminate = $callback($result, $url, $requestHeaders, $post, $returnResponseHeaders, $maxRecursionCount, $i);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function splitResponseHeaderAndBody(string $response, int $statusCode)
    {
        $headers = [];

        $split = strpos($response, "\r\n\r\n");
        $header = substr($response, 0, $split);
        $body = str_replace($header."\r\n\r\n", '', $response);

        foreach (explode("\r\n", $header) as $i => $strLine) {
            if (0 === $i) {
                $headers['http_code'] = $statusCode;
            } else {
                list($strKey, $varValue) = explode(': ', $strLine);
                $headers[$strKey] = $varValue;
            }
        }

        return [$headers, trim($body)];
    }

    /**
     * Creates a linebreak separated list of the headers in $arrHeaders -> see request() and postRequest().
     *
     * @return string
     */
    public function prepareHeaderArrayForPrint(array $headers)
    {
        $result = '';
        $i = 0;

        foreach ($headers as $strKey => $strValue) {
            $result .= "$strKey: $strValue";

            if ($i++ != \count($headers) - 1) {
                $result .= PHP_EOL;
            }
        }

        return $result;
    }

    /**
     * @return HttpRequestInterface|null
     */
    public function getHandle()
    {
        return $this->handle;
    }

    public function setHandle(HttpRequestInterface $handle)
    {
        $this->handle = $handle;
    }

    /**
     * Create the curl handle.
     *
     * @param $url
     *
     * @return CurlRequest
     */
    public function createCurlHandle($url)
    {
        $handle = $this->handle ?: new CurlRequest();
        $handle->init($url);
        $handle->setOption(CURLOPT_RETURNTRANSFER, true);
        $handle->setOption(CURLOPT_TIMEOUT, 10);

        return $handle;
    }

    /**
     * Prepare headers for curl handle.
     *
     * @return array
     */
    protected function prepareHeaders(array $headers)
    {
        $preparedHeaders = [];

        foreach ($headers as $strName => $varValue) {
            $preparedHeaders[] = $strName.': '.$varValue;
        }

        return $preparedHeaders;
    }
}

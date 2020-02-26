<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Curl;

use HeimrichHannot\UtilsBundle\Request\HttpRequestInterface;

class StubCurlRequest implements HttpRequestInterface
{
    protected $url = null;
    protected $type = 'GET';
    protected $responseHeader = false;
    /**
     * @var array
     */
    protected $header;
    /**
     * @var string
     */
    protected $body;

    /**
     * @var bool
     */
    protected $hasError = false;

    public function __construct()
    {
        return $this;
    }

    public function init($url): HttpRequestInterface
    {
        $this->url = $url;

        return $this;
    }

    public function setOption($name, $value): HttpRequestInterface
    {
        switch ($name) {
            case CURLOPT_CUSTOMREQUEST:
                $this->type = $value;

                break;

            case CURLOPT_HEADER:
                $this->responseHeader = $value;

                break;
        }

        return $this;
    }

    public function execute()
    {
        if ($this->hasError) {
            $this->header['http_code'] = 0;

            return false;
        }

        if ($this->responseHeader) {
            return $this->parse_array_to_headers($this->header).$this->body;
        }

        return $this->body;
    }

    public function getInfo($name)
    {
        switch ($name) {
            case CURLOPT_CUSTOMREQUEST:
                return $this->type;

            case CURLINFO_HTTP_CODE:
                return $this->header['http_code'];

            case CURLINFO_CONTENT_TYPE:
                return 'text/html';
        }
    }

    public function close()
    {
        // TODO: Implement close() method.
    }

    /**
     * @return StubCurlRequest
     */
    public function setResponseHeader(array $header)
    {
        $this->header = $header;

        return $this;
    }

    /**
     * @return StubCurlRequest
     */
    public function setResponseBody(string $body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return StubCurlRequest
     */
    public function setResponseError(bool $hasError)
    {
        $this->hasError = $hasError;

        return $this;
    }

    protected function parse_array_to_headers(array $headers)
    {
        $result = [];
        $delimiter = "\r\n";

        foreach ($headers as $name => $value) {
            $result[] = sprintf('%s: %s', $name, $value);
        }

        return implode($delimiter, $result).$delimiter.$delimiter;
    }
}

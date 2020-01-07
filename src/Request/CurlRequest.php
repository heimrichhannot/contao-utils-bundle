<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Request;

class CurlRequest implements HttpRequestInterface
{
    private $handle = null;

    /**
     * CurlRequest constructor.
     */
    public function __construct()
    {
        return $this;
    }

    public function init($url): HttpRequestInterface
    {
        $this->handle = curl_init($url);

        return $this;
    }

    public function setOption($name, $value): HttpRequestInterface
    {
        curl_setopt($this->handle, $name, $value);

        return $this;
    }

    public function execute()
    {
        return curl_exec($this->handle);
    }

    public function getInfo($name)
    {
        return curl_getinfo($this->handle, $name);
    }

    public function close()
    {
        curl_close($this->handle);
    }
}

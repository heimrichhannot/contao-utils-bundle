<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Curl;

use Contao\Config;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Request\CurlRequestUtil;

class CurlRequestUtilTest extends ContaoTestCase
{
    public function testCanBeInstantiated()
    {
        $curl = new CurlRequestUtil($this->mockContaoFramework(), $this->mockContainer());
        $this->assertInstanceOf(CurlRequestUtil::class, $curl);
    }

    public function testRequest()
    {
        $curl = new CurlRequestUtil($this->mockContaoFramework(), $this->mockContainer());

        $curl->setHandle($this->createNewHandle());
        $result = $curl->request('https://www.heimrich-hannot.de', [], true);
        $this->assertArrayHasKey(0, $result);
        $this->assertArrayHasKey(1, $result);
        $this->assertSame(200, $result[0]['http_code']);
        $this->assertSame('text/html; charset=utf-8', $result[0]['Content-Type']);
        $this->assertSame('Apache', $result[0]['Server']);

        $curl->setHandle($this->createNewHandle());
        $result = $curl->request('https://www.heimrich-hannot.de');
        $this->assertStringStartsWith('<!DOCTYPE html>', $result);

        $curl->setHandle($this->createNewHandle());
        Config::set('hpProxy', 'http://proxy:80');
        $result = $curl->request('https://www.heimrich-hannot.de');
        $this->assertStringStartsWith('<!DOCTYPE html>', $result);

        $handle = $this->createNewHandle();
        $handle->setResponseError(true);
        $curl->setHandle($handle);
        $this->assertFalse($result = $curl->request('https://www.heimrich-hannot.de', [], true));
    }

    public function testPostRequest()
    {
        $curl = new CurlRequestUtil($this->mockContaoFramework(), $this->mockContainer());

        $curl->setHandle($this->createNewHandle());
        $result = $curl->postRequest('https://www.heimrich-hannot.de', [CURLINFO_CONTENT_TYPE => 'text/html; charset=utf-8'], ['test' => 'test'], true);
        $this->assertArrayHasKey(0, $result);
        $this->assertArrayHasKey(1, $result);
        $this->assertSame(200, $result[0]['http_code']);
        $this->assertSame('text/html; charset=utf-8', $result[0]['Content-Type']);
        $this->assertSame('Apache', $result[0]['Server']);

        $curl->setHandle($this->createNewHandle());
        $result = $curl->postRequest('https://www.heimrich-hannot.de');
        $this->assertStringStartsWith('<!DOCTYPE html>', $result);

        $curl->setHandle($this->createNewHandle());
        Config::set('hpProxy', 'http://proxy:80');
        $result = $curl->postRequest('https://www.heimrich-hannot.de');
        $this->assertStringStartsWith('<!DOCTYPE html>', $result);

        $handle = $this->createNewHandle();
        $handle->setResponseError(true);
        $curl->setHandle($handle);
        $this->assertFalse($result = $curl->postRequest('https://www.heimrich-hannot.de', [], [], true));
    }

    public function testRecursiveGetRequest()
    {
        $curl = new CurlRequestUtil($this->mockContaoFramework(), $this->mockContainer());
        $curl->setHandle($this->createNewHandle());
        $result = $curl->recursiveGetRequest(1, function ($result, $url, $requestHeaders, $returnResponseHeaders, $maxRecursionCount) {
            return true;
        }, 'https://www.heimrich-hannot.de');
        $this->assertStringStartsWith('<!DOCTYPE html>', $result);

        $curl = new CurlRequestUtil($this->mockContaoFramework(), $this->mockContainer());
        $curl->setHandle($this->createNewHandle());
        $result = $curl->recursiveGetRequest(1, function ($result, $url, $requestHeaders, $returnResponseHeaders, $maxRecursionCount) {
            return false;
        }, 'https://www.heimrich-hannot.de');
        $this->assertStringStartsWith('<!DOCTYPE html>', $result);

        $curl = new CurlRequestUtil($this->mockContaoFramework(), $this->mockContainer());
        $curl->setHandle($this->createNewHandle());
        $result = $curl->recursiveGetRequest(1, function ($result, $url, $requestHeaders, $returnResponseHeaders, $maxRecursionCount) {
            return true;
        }, 'https://www.heimrich-hannot.de', [], true);
        $this->assertSame(2, \count($result));
        $this->assertStringStartsWith('<!DOCTYPE html>', $result[1]);

        $curl = new CurlRequestUtil($this->mockContaoFramework(), $this->mockContainer());
        $curl->setHandle($this->createNewHandle());
        $result = $curl->recursiveGetRequest(3, function ($result, $url, $requestHeaders, $returnResponseHeaders, $maxRecursionCount) {
            return false;
        }, 'https://www.heimrich-hannot.de');
        $this->assertStringStartsWith('<!DOCTYPE html>', $result);

        $curl = new CurlRequestUtil($this->mockContaoFramework(), $this->mockContainer());
        $curl->setHandle($this->createNewHandle());
        $result = $curl->recursiveGetRequest(3, function ($result, $url, $requestHeaders, $returnResponseHeaders, $maxRecursionCount, $i) {
            if (2 == $i) {
                return true;
            }

            return false;
        }, 'https://www.heimrich-hannot.de');
        $this->assertStringStartsWith('<!DOCTYPE html>', $result);
    }

    public function testRecursivelyPostRequest()
    {
        $curl = new CurlRequestUtil($this->mockContaoFramework(), $this->mockContainer());
        $curl->setHandle($this->createNewHandle());
        $result = $curl->recursivePostRequest(1, function ($result, $url, $requestHeaders, $returnResponseHeaders, $maxRecursionCount) {
            return true;
        }, 'https://www.heimrich-hannot.de');
        $this->assertStringStartsWith('<!DOCTYPE html>', $result);

        $curl = new CurlRequestUtil($this->mockContaoFramework(), $this->mockContainer());
        $curl->setHandle($this->createNewHandle());
        $result = $curl->recursivePostRequest(1, function ($result, $url, $requestHeaders, $returnResponseHeaders, $maxRecursionCount) {
            return false;
        }, 'https://www.heimrich-hannot.de');
        $this->assertStringStartsWith('<!DOCTYPE html>', $result);

        $curl = new CurlRequestUtil($this->mockContaoFramework(), $this->mockContainer());
        $curl->setHandle($this->createNewHandle());
        $result = $curl->recursivePostRequest(1, function ($result, $url, $requestHeaders, $returnResponseHeaders, $maxRecursionCount) {
            return true;
        }, 'https://www.heimrich-hannot.de', [], [], true);
        $this->assertSame(2, \count($result));
        $this->assertStringStartsWith('<!DOCTYPE html>', $result[1]);

        $curl = new CurlRequestUtil($this->mockContaoFramework(), $this->mockContainer());
        $curl->setHandle($this->createNewHandle());
        $result = $curl->recursivePostRequest(3, function ($result, $url, $requestHeaders, $returnResponseHeaders, $maxRecursionCount) {
            return false;
        }, 'https://www.heimrich-hannot.de');
        $this->assertStringStartsWith('<!DOCTYPE html>', $result);

        $curl = new CurlRequestUtil($this->mockContaoFramework(), $this->mockContainer());
        $curl->setHandle($this->createNewHandle());
        $result = $curl->recursivePostRequest(3, function ($result, $url, $requestHeaders, $returnResponseHeaders, $maxRecursionCount, $i) {
            if (2 == $i) {
                return true;
            }

            return false;
        }, 'https://www.heimrich-hannot.de');
        $this->assertStringStartsWith('<!DOCTYPE html>', $result);
    }

    public function testPrepareHeaders()
    {
        $curl = new CurlRequestUtil($this->mockContaoFramework(), $this->mockContainer());
        $curl->setHandle($this->createNewHandle());
        $result = $curl->request('https://www.heimrich-hannot.de', [
            'User-Agent', 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.5)',
            'Accept', 'text/html; charset=utf-8', ], true);
        $this->assertSame('text/html; charset=utf-8', $result[0]['Content-Type']);
    }

    public function testPrepareHeaderArrayForPrint()
    {
        $curl = new CurlRequestUtil($this->mockContaoFramework(), $this->mockContainer());
        $result = $curl->prepareHeaderArrayForPrint([CURLINFO_CONTENT_TYPE => 'text/plain', CURLINFO_CONTENT_LENGTH_DOWNLOAD => '100']);
        $this->assertSame(CURLINFO_CONTENT_TYPE.': text/plain'.PHP_EOL.CURLINFO_CONTENT_LENGTH_DOWNLOAD.': 100', $result);

        $result = $curl->prepareHeaderArrayForPrint([]);
        $this->assertSame('', $result);
    }

    public function testGetSetHandle()
    {
        $curl = new CurlRequestUtil($this->mockContaoFramework(), $this->mockContainer());
        $this->assertNull($curl->getHandle());
        $curl->postRequest('https://heimrich-hannot.de');
        $this->assertNull($curl->getHandle());
        $curl->setHandle($this->createNewHandle());
        $this->assertInstanceOf(StubCurlRequest::class, $curl->getHandle());
    }

    protected function createNewHandle()
    {
        $handle = new StubCurlRequest();
        $handle->setResponseHeader([
            'http_code' => 200,
            'Content-Type' => 'text/html; charset=utf-8',
            'Server' => 'Apache',
        ]);
        $handle->setResponseBody('<!DOCTYPE html>');

        return $handle;
    }
}

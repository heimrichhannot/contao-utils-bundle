<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Curl;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Curl\CurlUtil;

class CurlUtilTest extends ContaoTestCase
{
    public function testCreateCurlObject()
    {
        $curl = CurlUtil::createCurlObject('https://www.heimrich-hannot.de');
        $this->assertTrue(is_resource($curl));
    }

    public function testPrepareHeaderArrayForPrint()
    {
        $result = CurlUtil::prepareHeaderArrayForPrint([CURLINFO_CONTENT_TYPE => 'text/plain', CURLINFO_CONTENT_LENGTH_DOWNLOAD => '100']);
        $this->assertSame(CURLINFO_CONTENT_TYPE.': text/plain'.PHP_EOL.CURLINFO_CONTENT_LENGTH_DOWNLOAD.': 100', $result);

        $result = CurlUtil::prepareHeaderArrayForPrint([]);
        $this->assertSame('', $result);
    }

    public function testSplitResponseHeaderAndBody()
    {
        $objCurl = CurlUtil::createCurlObject('https://www.heimrich-hannot.de');
        curl_setopt($objCurl, CURLOPT_HEADER, true);
        $response = curl_exec($objCurl);
        $result = CurlUtil::splitResponseHeaderAndBody($response, 200);
        $this->assertArrayHasKey(0, $result);
        $this->assertArrayHasKey(1, $result);
        $this->assertSame(200, $result[0]['http_code']);
        $this->assertSame('text/html; charset=utf-8', $result[0]['Content-Type']);
        $this->assertSame('Apache', $result[0]['Server']);
    }

    public function testPostRequest()
    {
        $result = CurlUtil::postRequest('https://www.heimrich-hannot.de', [CURLINFO_CONTENT_TYPE => 'text/plain', CURLINFO_CONTENT_LENGTH_DOWNLOAD => '100'], ['test' => 'test'], true);
        $this->assertArrayHasKey(0, $result);
        $this->assertArrayHasKey(1, $result);
        $this->assertSame(400, $result[0]['http_code']);
        $this->assertSame('text/html; charset=UTF-8', $result[0]['Content-Type']);
        $this->assertSame('Apache', $result[0]['Server']);

        $result = CurlUtil::postRequest('https://www.heimrich-hannot.de');
        $this->assertStringStartsWith('<!DOCTYPE html>', $result);
    }

    public function testRecursivelyPostRequest()
    {
        $result = CurlUtil::recursivelyPostRequest(1, function ($varResult, $strUrl, $arrRequestHeaders, $arrPost, $blnReturnResponseHeaders, $intMaxRecursionCount) { return false; }, 'https://www.heimrich-hannot.de');
        $this->assertStringStartsWith('<!DOCTYPE html>', $result);

        $result = CurlUtil::recursivelyPostRequest(1, 'test', 'https://www.heimrich-hannot.de');
        $this->assertStringStartsWith('<!DOCTYPE html>', $result);
    }

    public function testRequest()
    {
        $obj = new CurlUtil();
        $result = $obj->request('https://www.heimrich-hannot.de', [CURLINFO_CONTENT_TYPE => 'text/plain', CURLINFO_CONTENT_LENGTH_DOWNLOAD => '100'], true);
        $this->assertArrayHasKey(0, $result);
        $this->assertArrayHasKey(1, $result);
        $this->assertSame(200, $result[0]['http_code']);
        $this->assertSame('text/html; charset=utf-8', $result[0]['Content-Type']);
        $this->assertSame('Apache', $result[0]['Server']);

        $result = $obj->request('https://www.heimrich-hannot.de');
        $this->assertStringStartsWith('<!DOCTYPE html>', $result);
    }
}

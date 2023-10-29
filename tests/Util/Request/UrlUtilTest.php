<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Util\Request;

use HeimrichHannot\UtilsBundle\Exception\InvalidUrlException;
use HeimrichHannot\UtilsBundle\Tests\AbstractUtilsTestCase;
use HeimrichHannot\UtilsBundle\Util\UrlUtil;
use PHPUnit\Framework\MockObject\MockBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class UrlUtilTest extends AbstractUtilsTestCase
{
    public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null)
    {
        $requestStack = $parameters['requestStack'] ?? $this->createMock(RequestStack::class);

        return new UrlUtil($requestStack);
    }

    public function removeQueryStringProvider()
    {
        return [
            ['https://example.com', '', 'https://example.com'],
            ['https://example.com?foo=bar', 'test', 'https://example.com?foo=bar'],
            ['https://example.com?foo=bar', 'foo', 'https://example.com'],
            ['https://example.com?test=1&foo=bar', 'foo', 'https://example.com?test=1'],
            ['https://example.com?foo=bar&test=1', 'foo', 'https://example.com?test=1'],
            ['https://example.com?foo=bar&test=1#dev=1', 'foo', 'https://example.com?test=1#dev=1'],
            ['https://example.com?foo=bar&test=1#dev=1', 'foo', 'https://example.com?test=1#dev=1'],
            ['?foo=bar&test=1#dev=1', 'foo', '?test=1#dev=1'],
        ];
    }

    /**
     * @dataProvider removeQueryStringProvider
     */
    public function testRemoveQueryStringParameterToUrl($url, $parameter, $result)
    {
        $instance = $this->getTestInstance();
        $this->assertSame($result, $instance->removeQueryStringParameterFromUrl($parameter, $url));

        $request = $this->createMock(Request::class);
        $request->method('getUri')->willReturn($url);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $instance = $this->getTestInstance(['requestStack' => $requestStack]);
        $this->assertSame($result, $instance->removeQueryStringParameterFromUrl($parameter));
    }

    public function addQueryStringParameterProvider()
    {
        return [
            ['https://example.com', '', 'https://example.com'],
            ['https://example.com?foo=bar', 'test=1', 'https://example.com?foo=bar&test=1'],
            ['https://example.com?foo=bar', 'foo=bar', 'https://example.com?foo=bar'],
            ['https://example.com?test=1&foo=bar', 'foo=rab', 'https://example.com?test=1&foo=rab'],
            ['https://example.com?test=1', 'foo', 'https://example.com?test=1&foo='],
            ['https://example.com?test=1#dev=1', 'foo=bar', 'https://example.com?test=1&foo=bar#dev=1'],
            ['?test=1#dev=1', 'foo=bar', '?test=1&foo=bar#dev=1'],
        ];
    }

    /**
     * @dataProvider addQueryStringParameterProvider
     */
    public function testAddQueryStringParameterToUrl($url, $parameter, $result)
    {
        $instance = $this->getTestInstance();
        $this->assertSame($result, $instance->addQueryStringParameterToUrl($parameter, $url));

        $request = $this->createMock(Request::class);
        $request->method('getUri')->willReturn($url);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $instance = $this->getTestInstance(['requestStack' => $requestStack]);
        $this->assertSame($result, $instance->addQueryStringParameterToUrl($parameter));
    }

    public function testMakeUrlRelative()
    {
        $instance = $this->getTestInstance();

        $this->assertSame('', $instance->makeUrlRelative('https://example.com'));
        $this->assertSame('/abcd', $instance->makeUrlRelative('https://example.com/abcd'));
        $this->assertSame('/abcd/index.php?foo=bar#dev=1', $instance->makeUrlRelative('https://example.com/abcd/index.php?foo=bar#dev=1'));
        $this->assertSame('/abcd/index.php?foo=bar#dev=1', $instance->makeUrlRelative('//example.com/abcd/index.php?foo=bar#dev=1'));
        $this->assertSame('abcd/index.php?foo=bar#dev=1', $instance->makeUrlRelative('http://example.com/abcd/index.php?foo=bar#dev=1', [
            'removeLeadingSlash' => true,
        ]));

        $exeption = false;

        try {
            $instance->makeUrlRelative('///');
        } catch (InvalidUrlException $e) {
            $exeption = true;
        }

        $this->assertTrue($exeption);
    }

    public function testWithoutRequest()
    {
        $instance = $this->getTestInstance();
        $this->assertSame('', $instance->addQueryStringParameterToUrl(''));
        $this->assertSame('', $instance->removeQueryStringParameterFromUrl(''));
    }
}

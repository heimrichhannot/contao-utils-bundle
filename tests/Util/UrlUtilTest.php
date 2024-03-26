<?php

namespace Util;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Util\UrlUtil;

class UrlUtilTest extends ContaoTestCase
{
    public function getTestInstance(array $parameters = []): UrlUtil
    {
        if (!isset($parameters['requestStack']))
        {
            $parameters['requestStack'] = $this->createMock('Symfony\Component\HttpFoundation\RequestStack');
        }

        return new UrlUtil($parameters['requestStack']);
    }

    public function testAddQueryStringParameterToUrl()
    {
        $url = 'https://example.com';
        $instance = $this->getTestInstance();

        $this->assertSame($instance->addQueryStringParameterToUrl('foo=bar', $url), $url . '?foo=bar');
        $this->assertSame($instance->addQueryStringParameterToUrl('foo=bar', $url . '?foo=baz'), $url . '?foo=bar');
        $this->assertSame($instance->addQueryStringParameterToUrl('foo=bar', $url . '?baz=fuzz'), $url . '?baz=fuzz&foo=bar');

        $this->assertSame($instance->addQueryStringParameterToUrl(['foo' => 'bar'], $url), $url . '?foo=bar');
        $this->assertSame($instance->addQueryStringParameterToUrl(['foo' => 'bar'], $url . '?foo=baz'), $url . '?foo=bar');
        $this->assertSame($instance->addQueryStringParameterToUrl(['foo' => 'bar', 'spam' => 'ham'], $url . '?foo=baz'), $url . '?foo=bar&spam=ham');
    }

    public function testRemoveQueryStringParameterFromUrl()
    {
        $url = 'https://example.com';
        $urlW = $url . '?foo=bar&spam=ham&eggs=baz';
        $instance = $this->getTestInstance();

        $this->assertSame($instance->removeQueryStringParameterFromUrl('foo', $urlW), $url . '?spam=ham&eggs=baz');
        $this->assertSame($instance->removeQueryStringParameterFromUrl('spam', $urlW), $url . '?foo=bar&eggs=baz');

        $this->assertSame($instance->removeQueryStringParameterFromUrl(['foo'], $urlW), $url . '?spam=ham&eggs=baz');
        $this->assertSame($instance->removeQueryStringParameterFromUrl(['spam'], $urlW), $url . '?foo=bar&eggs=baz');
        $this->assertSame($instance->removeQueryStringParameterFromUrl(['foo', 'spam'], $urlW), $url . '?eggs=baz');
    }
}
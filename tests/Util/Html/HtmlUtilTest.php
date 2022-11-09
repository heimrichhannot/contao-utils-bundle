<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Util\Html;

use HeimrichHannot\UtilsBundle\Tests\AbstractUtilsTestCase;
use HeimrichHannot\UtilsBundle\Util\Html\HtmlUtil;
use PHPUnit\Framework\MockObject\MockBuilder;

class HtmlUtilTest extends AbstractUtilsTestCase
{
    public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null)
    {
        return new HtmlUtil();
    }

    public function testGenerateAttributeString()
    {
        $instance = $this->getTestInstance();
        $this->assertEmpty($instance->generateAttributeString([]));
        $this->assertSame('a="b"', $instance->generateAttributeString(['a' => 'b']));
        $this->assertSame(
            'src="heimrich-hannot.de" type="text/website" async',
            $instance->generateAttributeString(['src' => 'heimrich-hannot.de', 'type' => 'text/website', 'async' => true])
        );
        $this->assertSame(
            'src="heimrich-hannot.de" type="text/website"',
            $instance->generateAttributeString(['src' => 'heimrich-hannot.de', 'type' => 'text/website', 'async' => false])
        );
        $this->assertSame(
            'src="heimrich-hannot.de" type="text/website" async="async"',
            $instance->generateAttributeString(['src' => 'heimrich-hannot.de', 'type' => 'text/website', 'async' => true], ['xhtml' => true])
        );
    }
}

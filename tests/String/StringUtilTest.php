<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\String;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\String\StringUtil;

class StringUtilTest extends ContaoTestCase
{
    public function setUp()
    {
        $container = $this->mockContainer();
        $container->set('contao.framework', $this->mockContaoFramework());

        System::setContainer($container);
    }

    public function testStartsWith()
    {
        $stringUtil = new StringUtil($this->mockContaoFramework());

        $resultTrue = $stringUtil->startsWith('This is a test string', 'This ');
        $resultFalse = $stringUtil->startsWith('This is a test string', 'ABC');

        $this->assertTrue($resultTrue);
        $this->assertFalse($resultFalse);
    }

    public function testEndsWith()
    {
        $stringUtil = new StringUtil($this->mockContaoFramework());

        $resultTrue = $stringUtil->endsWith('This is a test string', ' string');
        $resultFalse = $stringUtil->endsWith('This is a test string', 'ABC');

        $this->assertTrue($resultTrue);
        $this->assertFalse($resultFalse);
    }

    public function testCamelCaseToDashed()
    {
        $stringUtil = new StringUtil($this->mockContaoFramework());

        $result = $stringUtil->camelCaseToDashed('someCamelCase');

        $this->assertSame('some-camel-case', $result);
    }
}

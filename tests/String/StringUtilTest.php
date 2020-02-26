<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
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

    /**
     * @param null $mock
     *
     * @return StringUtil
     */
    public function createStringUtil(array $parameters = [], $mock = null)
    {
        if (!$mock) {
            $stringUtil = new StringUtil();
        } else {
            $stringUtil = $mock->setConstructorArgs()->getMock();
        }

        return $stringUtil;
    }

    public function testHtml2Text()
    {
        $html = '
        <html>
        <title>Ignored Title</title>
        <body>
          <h1>Hello, World!</h1>
        
          <p>This is some e-mail content.
          Even though it has whitespace and newlines, the e-mail converter
          will handle it correctly.
        
          <p>Even mismatched tags.</p>
        
          <div>A div</div>
          <div>Another div</div>
          <div>A div<div>within a div</div></div>
        
          <a href="http://foo.com">A link</a>
        
        </body>
        </html>';

        $expected =
'Hello, World!

This is some e-mail content. Even though it has whitespace and newlines, the e-mail converter will handle it correctly.

Even mismatched tags.

A div
Another div
A div
within a div
[A link](http://foo.com)';

        $stringUtil = $this->createStringUtil();

        $this->assertSame($expected, $stringUtil->html2Text($html));
    }

    public function testStartsWith()
    {
        $stringUtil = $this->createStringUtil();

        $resultTrue = $stringUtil->startsWith('This is a test string', 'This ');
        $resultFalse = $stringUtil->startsWith('This is a test string', 'ABC');

        $this->assertTrue($resultTrue);
        $this->assertFalse($resultFalse);
    }

    public function testEndsWith()
    {
        $stringUtil = $this->createStringUtil();

        $resultTrue = $stringUtil->endsWith('This is a test string', ' string');
        $resultFalse = $stringUtil->endsWith('This is a test string', 'ABC');

        $this->assertTrue($resultTrue);
        $this->assertFalse($resultFalse);
    }

    public function testCamelCaseToDashed()
    {
        $stringUtil = $this->createStringUtil();

        $result = $stringUtil->camelCaseToDashed('someCamelCase');

        $this->assertSame('some-camel-case', $result);
    }

    public function testPregReplaceLast()
    {
        $stringUtil = $this->createStringUtil();

        $result = $stringUtil->pregReplaceLast('@_[a-f0-9]{13}@', 'dastusteeubfstz238572');
        $this->assertSame('dastusteeubfstz238572', $result);

        $result = $stringUtil->pregReplaceLast('', 'dasusteufb343ubf23');
        $this->assertSame('dasusteufb343ubf23', $result);
    }

    /**
     * @dataProvider truncateHtmlProvider
     */
    public function testTruncateHtml($text, $expected, $length, $expectedTextLength, $ending, $exact, $considerHtml)
    {
        $stringUtil = $this->createStringUtil();
        $result = $stringUtil->truncateHtml($text, (int) $length, $ending, $exact, $considerHtml);
        $this->assertSame($expected, $result);
        $this->assertSame($expectedTextLength, \strlen(strip_tags($result)));
    }

    public function truncateHtmlProvider()
    {
        return [
            [
                '<p><strong>Pellentesque</strong> habitant morbi&nbsp;tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. <a href="http://test.com"><span>Mauris</span></a> placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, commodo vitae, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. Donec non enim in turpis pulvinar facilisis. Ut felis. Praesent dapibus, neque id cursus faucibus, tortor neque egestas augue, eu vulputate magna eros eu erat. Aliquam erat volutpat. Nam dui mi, tincidunt quis, accumsan porttitor, facilisis luctus, metus.</p>',
                '<p><strong>Pellentesque</strong> habitant morbi&nbsp;tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae&nbsp;&hellip;</p>',
                260,
                262,
                '&nbsp;&hellip;',
                false,
                true,
            ],
            [
                '<p><strong>Pellentesque</strong> habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. <a href="http://test.com"><span>Mauris</span></a> placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, commodo vitae, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. Donec non enim in turpis pulvinar facilisis. Ut felis. Praesent dapibus, neque id cursus faucibus, tortor neque egestas augue, eu vulputate magna eros eu erat. Aliquam erat volutpat. Nam dui mi, tincidunt quis, accumsan porttitor, facilisis luctus, metus.</p>',
                '<p><strong>Pellentesque</strong> habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. <a href="http://test.com"><span>Mauris</span></a>&nbsp;&hellip;</p>',
                270,
                269,
                '&nbsp;&hellip;',
                false,
                true,
            ],
            [
                '<p><strong>Pellentesque</strong> habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. <a href="http://test.com"><span>Mauris</span></a> placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, commodo vitae, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. Donec non enim in turpis pulvinar facilisis. Ut felis. Praesent dapibus, neque id cursus faucibus, tortor neque egestas augue, eu vulputate magna eros eu erat. Aliquam erat volutpat. Nam dui mi, tincidunt quis, accumsan porttitor, facilisis luctus, metus.</p>',
                '<p><strong>Pellentesque</strong> habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae es&nbsp;&hellip;</p>',
                260,
                260,
                '&nbsp;&hellip;',
                true,
                true,
            ],
            [
                '<p><strong>Pellentesque</strong> habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. <a href="http://test.com"><span>Mauris</span></a> placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, commodo vitae, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. Donec non enim in turpis pulvinar facilisis. Ut felis. Praesent dapibus, neque id cursus faucibus, tortor neque egestas augue, eu vulputate magna eros eu erat. Aliquam erat volutpat. Nam dui mi, tincidunt quis, accumsan porttitor, facilisis luctus, metus.</p>',
                '<p><strong>Pellentesque</strong> habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies&nbsp;&hellip;',
                270,
                248,
                '&nbsp;&hellip;',
                false,
                false,
            ],
            [
                '<p><strong>Pellentesque</strong> habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. </p>',
                '<p><strong>Pellentesque</strong> habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. </p>',
                270,
                94,
                '&nbsp;&hellip;',
                false,
                true,
            ],
            [
                '<p><strong>Pellentesque</strong> habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. </p>',
                '<p><strong>Pellentesque</strong> habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. </p>',
                270,
                94,
                '&nbsp;&hellip;',
                false,
                false,
            ],
        ];
    }

    public function testCamelCaseToSnake()
    {
        $stringUtil = $this->createStringUtil();
        $this->assertSame('some_camel_case', $stringUtil->camelCaseToSnake('someCamelCase'));
        $this->assertSame('my_pretty_class', $stringUtil->camelCaseToSnake('MyPrettyClass'));
        $this->assertSame('my_pretty_class', $stringUtil->camelCaseToSnake('my_pretty_class'));
    }
}

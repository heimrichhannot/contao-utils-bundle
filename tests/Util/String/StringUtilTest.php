<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Util\String;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Util\Type\StringUtil;

class StringUtilTest extends ContaoTestCase
{
    public function getTestInstance(array $parameters = [])
    {
        $framework = $this->mockContaoFramework();

        return new StringUtil($framework);
    }

    public function testStartsWith()
    {
        $instance = $this->getTestInstance();
        $this->assertTrue($instance->startsWith('', ''));
        $this->assertTrue($instance->startsWith('bla', ''));
        $this->assertTrue($instance->startsWith('heimrichhannot', 'h'));
        $this->assertTrue($instance->startsWith('heimrichhannot', 'heimrich'));
        $this->assertFalse($instance->startsWith('heimrichhannot', 'hannot'));
        $this->assertFalse($instance->startsWith('heimrichhannot', 'foo'));
        $this->assertFalse($instance->startsWith('heimrichhannot', 'heimrichhannotutils'));
    }

    public function testEndsWith()
    {
        $instance = $this->getTestInstance();
        $this->assertTrue($instance->endsWith('', ''));
        $this->assertTrue($instance->endsWith('bla', ''));
        $this->assertTrue($instance->endsWith('heimrichhannot', 't'));
        $this->assertTrue($instance->endsWith('heimrichhannot', 'hannot'));
        $this->assertFalse($instance->endsWith('heimrichhannot', 'heimrich'));
        $this->assertFalse($instance->endsWith('heimrichhannot', 'foo'));
        $this->assertFalse($instance->endsWith('heimrichhannot', 'hannotutils'));
        $this->assertFalse($instance->endsWith('heimrichhannot', 'heimrichhannotutils'));
        $this->assertTrue($instance->endsWith('This is a test string', 'string'));
        $this->assertFalse($instance->endsWith('This is a test string', 'ABC'));
    }

    public function testCamelCaseToDashed()
    {
        $instance = $this->getTestInstance();
        $this->assertSame('some-class', $instance->camelCaseToDashed('SomeClass'));
        $this->assertSame('some-class', $instance->camelCaseToDashed('some-class'));
        $this->assertSame('someclass', $instance->camelCaseToDashed('someclass'));
        $this->assertSame('someclass', $instance->camelCaseToDashed('Someclass'));
        $this->assertSame('some-class', $instance->camelCaseToDashed('Some-Class'));
    }

    public function testCamelCaseToSnake()
    {
        $instance = $this->getTestInstance();
        $this->assertSame('some_class', $instance->camelCaseToSnake('SomeClass'));
        $this->assertSame('some-class', $instance->camelCaseToDashed('some-class'));
        $this->assertSame('someclass', $instance->camelCaseToSnake('someclass'));
        $this->assertSame('someclass', $instance->camelCaseToSnake('Someclass'));
        $this->assertSame('some-class', $instance->camelCaseToSnake('Some-Class'));
        $this->assertSame('some_camel_case', $instance->camelCaseToSnake('someCamelCase'));
        $this->assertSame('my_pretty_class', $instance->camelCaseToSnake('MyPrettyClass'));
        $this->assertSame('my_pretty_class', $instance->camelCaseToSnake('my_pretty_class'));
    }

    public function testRandomChar()
    {
        $instance = $this->getTestInstance();
        $this->assertSame('D', $instance->randomChar(false, ['randomNumberGenerator' => function ($min, $max) { return 3; }]));
        $this->assertSame('d', $instance->randomChar(false, ['randomNumberGenerator' => function ($min, $max) { return 25; }]));
        $this->assertSame('5', $instance->randomChar(false, ['randomNumberGenerator' => function ($min, $max) { return 46; }]));
        $this->assertSame('Z', $instance->randomChar(true, ['randomNumberGenerator' => function ($min, $max) { return 25; }]));
        $this->assertSame('y', $instance->randomChar(true, ['randomNumberGenerator' => function ($min, $max) { return 50; }]));
        $this->assertSame('0', $instance->randomChar(true, ['randomNumberGenerator' => function ($min, $max) { return 52; }]));
        $this->assertInternalType('string', $instance->randomChar());
        $this->expectException(\UnexpectedValueException::class);
        $instance->randomLetter(true, ['randomNumberGenerator' => function ($min, $max) { return 62; }]);
    }

    public function testRandomLetter()
    {
        $instance = $this->getTestInstance();
        $this->assertSame('D', $instance->randomLetter(false, ['randomNumberGenerator' => function ($min, $max) { return 3; }]));
        $this->assertSame('d', $instance->randomLetter(false, ['randomNumberGenerator' => function ($min, $max) { return 25; }]));
        $this->assertSame('Z', $instance->randomLetter(true, ['randomNumberGenerator' => function ($min, $max) { return 25; }]));
        $this->assertSame('y', $instance->randomLetter(true, ['randomNumberGenerator' => function ($min, $max) { return 50; }]));
        $this->assertInternalType('string', $instance->randomLetter());
        $this->expectException(\UnexpectedValueException::class);
        $instance->randomLetter(true, ['randomNumberGenerator' => function ($min, $max) { return 52; }]);
    }

    public function testRandomNumber()
    {
        $instance = $this->getTestInstance();
        $this->assertSame('2', $instance->randomNumber(false, ['randomNumberGenerator' => function ($min, $max) { return 0; }]));
        $this->assertSame('7', $instance->randomNumber(false, ['randomNumberGenerator' => function ($min, $max) { return 5; }]));
        $this->assertSame('0', $instance->randomNumber(true, ['randomNumberGenerator' => function ($min, $max) { return 0; }]));
        $this->assertSame('5', $instance->randomNumber(true, ['randomNumberGenerator' => function ($min, $max) { return 5; }]));
        $this->assertInternalType('string', $instance->randomNumber());
        $this->expectException(\UnexpectedValueException::class);
        $instance->randomNumber(true, ['randomNumberGenerator' => function ($min, $max) { return 10; }]);
    }

    public function testRandom()
    {
        $instance = $this->getTestInstance();
        $this->assertSame('', $instance->random(''));
        $this->assertSame('a', $instance->random('a'));
        $this->assertSame('c', $instance->random('abc', ['randomNumberGenerator' => function ($min, $max) { return 2; }]));
        $this->assertInternalType('string', $instance->random('abc'));
        $this->expectException(\UnexpectedValueException::class);
        $instance->random(true, ['randomNumberGenerator' => function ($min, $max) { return 10; }]);
    }

    public function providerTruncateHtml()
    {
        return [
            [
                '<p>Hallo Welt!</p><p>Lorem ipsum!</p>',
                '<p>Hallo Welt!…</p>',
                11,
            ],
            [
                '<p>Hallo Welt!</p><p>Lorem ipsum!</p>',
                '<p>Hallo Welt!</p>',
                11,
                '',
            ],
            [
                '<p>Hallo Welt!</p><p>Lorem ipsum!</p>',
                '<p>Hallo Welt!&nbsp;&hellip;</p>',
                11,
                '&nbsp;&hellip;',
            ],
            [
                '<p>Hallo Welt!</p><p>Lorem ipsum!</p>',
                '<p>Hallo Welt!</p>',
                14,
                '',
            ],
            [
                '<p>Hallo Welt!</p><p>Lorem ipsum!</p>',
                '<p>Hallo Welt!…</p>',
                14,
            ],
            [
                '<p>Hallo Welt!</p><p>Lorem ipsum!</p>',
                '<p>Hallo Welt!</p><p>Lor</p>',
                14,
                '',
                true,
            ],
            [
                '<p>Hallo Welt!</p><p>Lorem ipsum!</p>',
                '<p>…</p>',
                1,
            ],
            [
                '<p><strong>Pellentesque</strong> habitant morbi&nbsp;tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. <a href="http://test.com"><span>Mauris</span></a> placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, commodo vitae, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. Donec non enim in turpis pulvinar facilisis. Ut felis. Praesent dapibus, neque id cursus faucibus, tortor neque egestas augue, eu vulputate magna eros eu erat. Aliquam erat volutpat. Nam dui mi, tincidunt quis, accumsan porttitor, facilisis luctus, metus.</p>',
                "<p><strong>Pellentesque</strong> habitant morbi\u{a0}tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. <a href=\"http://test.com\"><span>Mauris</span></a>…</p>",
                260,
            ],
            [
                '<p><strong>Pellentesque</strong> habitant morbi&nbsp;tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. <a href="http://test.com"><span>Mauris</span></a> placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, commodo vitae, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. Donec non enim in turpis pulvinar facilisis. Ut felis. Praesent dapibus, neque id cursus faucibus, tortor neque egestas augue, eu vulputate magna eros eu erat. Aliquam erat volutpat. Nam dui mi, tincidunt quis, accumsan porttitor, facilisis luctus, metus.</p>',
                "<p><strong>Pellentesque</strong> habitant morbi\u{a0}tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. <a href=\"http://test.com\"><span>Mauris</span></a> plac</p>",
                260,
                '',
                true,
            ],
            [
                '<p><strong>Pellentesque</strong> habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. </p>',
                '<p><strong>Pellentesque</strong> habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. </p>',
                270,
            ],
            [
                '<a>Lorem ipsum dolor sit amet.</a>',
                '<a>Lorem ipsum</a>…',
                12,
            ],
            [
                '<span><a>Lorem ipsum dolor sit <span>amet</span>.</a> consetetur sadipscing elitr</span>',
                '<span><a>Lorem ipsum</a>…</span>',
                12,
            ],
        ];
    }

    /**
     * @dataProvider providerTruncateHtml
     */
    public function testTruncateHtml(string $html, string $expected, int $limit, string $ellipsis = '…', bool $exact = false)
    {
        $instance = $this->getTestInstance();
        $options = [];

        if ($exact) {
            $options['exact'] = true;
        }

        $this->assertSame($expected, $instance->truncateHtml($html, $limit, $ellipsis, $options));
    }

    public function testPregReplaceLast()
    {
        $stringUtil = $this->getTestInstance();

        $result = $stringUtil->pregReplaceLast('@_[a-f0-9]{13}@', 'dastusteeubfstz238572');
        $this->assertSame('dastusteeubfstz238572', $result);

        $result = $stringUtil->pregReplaceLast('', 'dasusteufb343ubf23');
        $this->assertSame('dasusteufb343ubf23', $result);

        $result = $stringUtil->pregReplaceLast('~text~', 'text abcd text text efgh');
        $this->assertSame('text abcd text  efgh', $result);

        $result = $stringUtil->pregReplaceLast('~text~', 'text abcd text text efgh', 'bar');
        $this->assertSame('text abcd text bar efgh', $result);
    }

    public function testConvertXmlToArray()
    {
        $instance = $this->getTestInstance();

        $this->assertArrayHasKey('hello',
            $instance->convertXmlToArray('<root><hello>world</hello></root>')
        );
        $this->assertArrayHasKey('hello',
            $instance->convertXmlToArray('<root><hello>world</hello><foo><bar>classic</bar></foo><cdata><![CDATA[<html>Can be problematic to parse!</html>]]></cdata></root>')
        );
    }

    public function testRemoveLeadingString()
    {
        $instance = $this->getTestInstance();
        $this->assertSame(
            'Lorem ipsum dolor sit amet.',
            $instance->removeLeadingString('ipsum', 'Lorem ipsum dolor sit amet.')
        );
        $this->assertSame(
            'ipsum dolor sit amet.',
            $instance->removeLeadingString('Lorem', 'Lorem ipsum dolor sit amet.')
        );
        $this->assertSame(
            ' ipsum dolor sit amet.',
            $instance->removeLeadingString('Lorem', 'Lorem ipsum dolor sit amet.', ['trim' => false])
        );
    }

    public function testRemoveTrailingString()
    {
        $instance = $this->getTestInstance();
        $this->assertSame(
            'Lorem ipsum dolor sit amet',
            $instance->removeTrailingString('ipsum', 'Lorem ipsum dolor sit amet')
        );
        $this->assertSame(
            'Lorem ipsum dolor sit',
            $instance->removeTrailingString('amet', 'Lorem ipsum dolor sit amet')
        );
        $this->assertSame(
            'Lorem ipsum dolor sit ',
            $instance->removeTrailingString('amet', 'Lorem ipsum dolor sit amet', ['trim' => false])
        );
    }
}

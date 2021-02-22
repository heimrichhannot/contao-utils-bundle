<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Util\String;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Util\String\StringUtil;

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

    public function testTruncateHtml()
    {
        $instance = $this->getTestInstance();
        $this->assertSame('<p>Hallo Welt!</p>', $instance->truncateHtml('<p>Hallo Welt! Lorem ipsum!</p>', 11));
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

        $stringUtil = $this->getTestInstance();

        $this->assertSame($expected, $stringUtil->html2Text($html));
    }
}

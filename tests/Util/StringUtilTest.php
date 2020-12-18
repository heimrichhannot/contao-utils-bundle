<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
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
    }

//    public function testCamelCaseToDashed()
//    {
//        $instance = $this->getTestInstance();
//        $this->assertSame('some-class', $instance->camelCaseToDashed('SomeClass'));
//        $this->assertSame('some-class', $instance->camelCaseToDashed('some-class'));
//        $this->assertSame('someclass', $instance->camelCaseToDashed('someclass'));
//        $this->assertSame('someclass', $instance->camelCaseToDashed('Someclass'));
//        $this->assertSame('some-class', $instance->camelCaseToDashed('Some-Class'));
//    }
//
//    public function testCamelCaseToSnake()
//    {
//        $instance = $this->getTestInstance();
//        $this->assertSame('some_class', $instance->camelCaseToDashed('SomeClass'));
////        Fails in the
////        $this->assertSame('some-class', $instance->camelCaseToDashed('some-class'));
//        $this->assertSame('someclass', $instance->camelCaseToDashed('someclass'));
//        $this->assertSame('someclass', $instance->camelCaseToDashed('Someclass'));
//        $this->assertSame('some-class', $instance->camelCaseToDashed('Some-Class'));
//    }
}

<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Classes;

use HeimrichHannot\UtilsBundle\Arrays\ArrayUtil;
use HeimrichHannot\UtilsBundle\Classes\ClassUtil;
use HeimrichHannot\UtilsBundle\Classes\JsonSerializeTestClass;
use HeimrichHannot\UtilsBundle\Tests\AbstractUtilsTestCase;
use HeimrichHannot\UtilsBundle\Util\String\StringUtil;
use PHPUnit\Framework\MockObject\MockBuilder;

class ClassUtilTest extends AbstractUtilsTestCase
{
    public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null)
    {
        $arrayUtil = $parameters['arrayUtil'] ?? $this->createMock(ArrayUtil::class);
        $stringUtil = $parameters['stringUtil'] ?? $this->createMock(StringUtil::class);

        return new ClassUtil($arrayUtil, $stringUtil);
    }

    public function testClassesInNamespace()
    {
        $stringUtilMock = $this->createMock(StringUtil::class);
        $stringUtilMock->method('startsWith')->willReturnCallback(function (string $haystack, string $needle) {
            return '' === $needle || false !== strrpos($haystack, $needle, -\strlen($haystack));
        });
        $classUtil = $this->getTestInstance([
            'stringUtil' => $stringUtilMock,
        ]);

        $classes = $classUtil->getClassesInNamespace('HeimrichHannot\UtilsBundle\Arrays');
        $this->assertSame(['HeimrichHannot\UtilsBundle\Arrays\ArrayUtil' => 'HeimrichHannot\UtilsBundle\Arrays\ArrayUtil'], $classes);
    }

    public function testGetChildClasses()
    {
        $this->markTestSkipped();
        $classUtil = $this->getTestInstance();
        $childClasses = $classUtil->getChildClasses('HeimrichHannot\UtilsBundle\Choice\AbstractChoice');
        $this->assertSame([
            'HeimrichHannot\UtilsBundle\Choice\DataContainerChoice' => 'HeimrichHannot\UtilsBundle\Choice\DataContainerChoice',
            'HeimrichHannot\UtilsBundle\Choice\FieldChoice' => 'HeimrichHannot\UtilsBundle\Choice\FieldChoice',
            'HeimrichHannot\UtilsBundle\Choice\MessageChoice' => 'HeimrichHannot\UtilsBundle\Choice\MessageChoice',
            'HeimrichHannot\UtilsBundle\Choice\ModelInstanceChoice' => 'HeimrichHannot\UtilsBundle\Choice\ModelInstanceChoice',
            'HeimrichHannot\UtilsBundle\Choice\TwigTemplateChoice' => 'HeimrichHannot\UtilsBundle\Choice\TwigTemplateChoice',
        ], $childClasses);
    }

    public function testGetConstantByPrefixes()
    {
        $this->markTestSkipped();
        $classUtil = $this->getTestInstance();
        $constants = $classUtil->getConstantsByPrefixes('HeimrichHannot\UtilsBundle\Dca\DcaUtil', ['AUTHOR_TYPE']);
        $this->assertSame(['none' => 'none', 'member' => 'member', 'user' => 'user'], $constants);

        $constants = $classUtil->getConstantsByPrefixes('HeimrichHannot\UtilsBundle\Dca\DcaUaftil', ['AUTHOR_TYPE']);
        $this->assertSame([], $constants);

        $constants = $classUtil->getConstantsByPrefixes('HeimrichHannot\UtilsBundle\Dca\DcaUtil', ['NO_CONST']);
        $this->assertSame([], $constants);
    }

    public function testGetParentClasses()
    {
        $classUtil = $this->getTestInstance();
        $classes = $classUtil->getParentClasses('HeimrichHannot\UtilsBundle\Choice\DataContainerChoice');
        $this->assertSame(['HeimrichHannot\UtilsBundle\Choice\AbstractChoice'], $classes);
    }

    public function testJsonSerialize()
    {
        if (!class_exists('HeimrichHannot\UtilsBundle\Tests\Classes\JsonSerializeTestClass')) {
            include_once __DIR__.'/JsonSerializeTestClass.php';
        }

        $classUtil = $this->getTestInstance();

        $testClass = new JsonSerializeTestClass();

        $result = $classUtil->jsonSerialize($testClass);

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('map', $result);
        $this->assertArrayHasKey('isPublished', $result);
        $this->assertTrue($result['isPublished']);
        $this->assertArrayHasKey('hasPublished', $result);
        $this->assertFalse($result['hasPublished']);
        $this->assertArrayHasKey('addDetails', $result);
        $this->assertTrue($result['addDetails']);
        $this->assertArrayNotHasKey('protectedMap', $result);
        $this->assertArrayHasKey('mapWithAttributes', $result);
    }
}

<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Classes;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Arrays\ArrayUtil;
use HeimrichHannot\UtilsBundle\Classes\ClassUtil;
use HeimrichHannot\UtilsBundle\Classes\JsonSerializeTestClass;
use HeimrichHannot\UtilsBundle\String\StringUtil;

class ClassUtilTest extends ContaoTestCase
{
    public function setUp()
    {
        parent::setUp();

        $container = $this->mockContainer();
        $container->set('contao.framework', $this->mockContaoFramework([]));
        $container->set('huh.utils.string', new StringUtil($this->mockContaoFramework()));
        $container->set('huh.utils.array', new ArrayUtil($this->mockContaoFramework()));
        System::setContainer($container);
    }

    public function testClassesInNamespace()
    {
        $classUtil = new ClassUtil();
        $classes = $classUtil->getClassesInNamespace('HeimrichHannot\UtilsBundle\Arrays');
        $this->assertSame(['HeimrichHannot\UtilsBundle\Arrays\ArrayUtil' => 'HeimrichHannot\UtilsBundle\Arrays\ArrayUtil'], $classes);
    }

    public function testGetChildClasses()
    {
        $classUtil = new ClassUtil();
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
        $classUtil = new ClassUtil();
        $constants = $classUtil->getConstantsByPrefixes('HeimrichHannot\UtilsBundle\Dca\DcaUtil', ['AUTHOR_TYPE']);
        $this->assertSame(['none' => 'none', 'member' => 'member', 'user' => 'user'], $constants);

        $constants = $classUtil->getConstantsByPrefixes('HeimrichHannot\UtilsBundle\Dca\DcaUaftil', ['AUTHOR_TYPE']);
        $this->assertSame([], $constants);

        $constants = $classUtil->getConstantsByPrefixes('HeimrichHannot\UtilsBundle\Dca\DcaUtil', ['NO_CONST']);
        $this->assertSame([], $constants);
    }

    public function testGetParentClasses()
    {
        $classUtil = new ClassUtil();
        $classes = $classUtil->getParentClasses('HeimrichHannot\UtilsBundle\Choice\DataContainerChoice');
        $this->assertSame(['HeimrichHannot\UtilsBundle\Choice\AbstractChoice'], $classes);
    }

    public function testJsonSerialize()
    {
        if (!\class_exists('HeimrichHannot\UtilsBundle\Tests\Classes\JsonSerializeTestClass')) {
            include_once __DIR__.'/JsonSerializeTestClass.php';
        }

        $classUtil = new ClassUtil();

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
        $this->assertArrayNotHasKey('mapWithAttributes', $result);
    }
}

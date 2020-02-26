<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Classes;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Arrays\ArrayUtil;
use HeimrichHannot\UtilsBundle\Classes\ClassUtil;
use HeimrichHannot\UtilsBundle\Classes\JsonSerializeTestClass;
use HeimrichHannot\UtilsBundle\String\StringUtil;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ClassUtilTest extends ContaoTestCase
{
    public function testClassesInNamespace()
    {
        $classUtil = new ClassUtil($this->getContainerMock());
        $classes = $classUtil->getClassesInNamespace('HeimrichHannot\UtilsBundle\Arrays');
        $this->assertSame(['HeimrichHannot\UtilsBundle\Arrays\ArrayUtil' => 'HeimrichHannot\UtilsBundle\Arrays\ArrayUtil'], $classes);
    }

    public function testGetChildClasses()
    {
        $classUtil = new ClassUtil($this->getContainerMock());
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
        $classUtil = new ClassUtil($this->getContainerMock());
        $constants = $classUtil->getConstantsByPrefixes('HeimrichHannot\UtilsBundle\Dca\DcaUtil', ['AUTHOR_TYPE']);
        $this->assertSame(['none' => 'none', 'member' => 'member', 'user' => 'user'], $constants);

        $constants = $classUtil->getConstantsByPrefixes('HeimrichHannot\UtilsBundle\Dca\DcaUaftil', ['AUTHOR_TYPE']);
        $this->assertSame([], $constants);

        $constants = $classUtil->getConstantsByPrefixes('HeimrichHannot\UtilsBundle\Dca\DcaUtil', ['NO_CONST']);
        $this->assertSame([], $constants);
    }

    public function testGetParentClasses()
    {
        $classUtil = new ClassUtil($this->getContainerMock());
        $classes = $classUtil->getParentClasses('HeimrichHannot\UtilsBundle\Choice\DataContainerChoice');
        $this->assertSame(['HeimrichHannot\UtilsBundle\Choice\AbstractChoice'], $classes);
    }

    public function testJsonSerialize()
    {
        if (!class_exists('HeimrichHannot\UtilsBundle\Tests\Classes\JsonSerializeTestClass')) {
            include_once __DIR__.'/JsonSerializeTestClass.php';
        }

        $classUtil = new ClassUtil($this->getContainerMock());

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

    /**
     * @param ContaoFramework $framework
     *
     * @return ContainerBuilder|ContainerInterface
     */
    protected function getContainerMock(ContainerBuilder $container = null, $framework = null)
    {
        if (!$container) {
            $container = $this->mockContainer();
        }

        if (!$framework) {
            $framework = $this->mockContaoFramework();
        }
        $container->set('contao.framework', $framework);

        $container->set('huh.utils.string', new StringUtil($this->mockContaoFramework()));
        $container->set('huh.utils.array', new ArrayUtil($container));

        return $container;
    }
}

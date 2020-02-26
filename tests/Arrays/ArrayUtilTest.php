<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Arrays;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Arrays\ArrayUtil;
use HeimrichHannot\UtilsBundle\String\StringUtil;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ArrayUtilTest extends ContaoTestCase
{
    protected function setUp()
    {
        parent::setUp();

        if (!\function_exists('array_insert')) {
            include_once __DIR__.'/../../vendor/contao/core-bundle/src/Resources/contao/helper/functions.php';
        }
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $instance = new ArrayUtil($this->getContainerMock());
        $this->assertInstanceOf(ArrayUtil::class, $instance);
    }

    public function testAasort()
    {
        $arrayUtil = new ArrayUtil($this->getContainerMock());

        $array = [0 => ['filename' => 'testfile3'], 1 => ['filename' => 'testfile1'], 2 => ['filename' => 'testfile2']];

        $arrayUtil->aasort($array, 'filename');
        $this->assertSame([1 => ['filename' => 'testfile1'], 2 => ['filename' => 'testfile2'], 0 => ['filename' => 'testfile3']], $array);
    }

    public function testRemoveValue()
    {
        $arrayUtil = new ArrayUtil($this->getContainerMock());

        $array = [0 => 0, 1 => 1, 2 => 2];
        $result = $arrayUtil->removeValue(1, $array);
        $this->assertTrue($result);
        $this->assertCount(2, $array);
        $this->assertArrayHasKey(0, $array);
        $this->assertArrayHasKey(2, $array);

        $result = $arrayUtil->removeValue(1, $array);
        $this->assertFalse($result);
    }

    public function testFilterByPrefixes()
    {
        $arrayUtil = new ArrayUtil($this->getContainerMock());

        $array = ['ls_0' => 0, 1 => 1, 2 => 2];
        $result = $arrayUtil->filterByPrefixes($array);
        $this->assertSame($array, $result);

        $result = $arrayUtil->filterByPrefixes($array, [1]);
        $this->assertSame([], $result);

        $result = $arrayUtil->filterByPrefixes($array, ['ls']);
        $this->assertSame(['ls_0' => 0], $result);
    }

    public function testRemovePrefix()
    {
        $arrayUtil = new ArrayUtil($this->getContainerMock());

        $array = ['ls_prefix_1' => 1];
        $result = $arrayUtil->removePrefix('ls_', $array);
        $this->assertSame(['prefix_1' => 1], $result);
    }

    public function testInsertInArrayByName()
    {
        $arrayUtil = new ArrayUtil($this->getContainerMock());

        $target = ['hello' => 'world'];
        $arrayUtil->insertInArrayByName($target, 'foo', 'bar');
        $this->assertSame(['hello' => 'world'], $target);

        $target = ['hello' => 'world'];
        $arrayUtil->insertInArrayByName($target, 'hello', 'foobar');
        $this->assertSame(['foobar', 'hello' => 'world'], $target);

        $target = ['hello' => 'world'];
        $arrayUtil->insertInArrayByName($target, 'hello', 'foobar', 1);
        $this->assertSame(['hello' => 'world', 'foobar'], $target);

        $target = [0 => 'world'];
        $arrayUtil->insertInArrayByName($target, '0', 'foobar', 1);
        $this->assertSame(['world', 'foobar'], $target);

        $target = [0 => 'world'];
        $arrayUtil->insertInArrayByName($target, '0', 'foobar', 1, true);
        $this->assertSame(['world'], $target);
    }

    public function testArrayToObject()
    {
        $arrayUtil = new ArrayUtil($this->getContainerMock());
        $result = $arrayUtil->arrayToObject([]);
        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertCount(0, (array) $result);

        $result = $arrayUtil->arrayToObject(['id' => 4, 'title' => 'Hallo Welt!']);
        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertCount(2, (array) $result);
        $this->assertSame('Hallo Welt!', $result->title);

        $result = $arrayUtil->arrayToObject(['id' => 4, 'title' => 'Hallo Welt!', 'content' => ['a', 'b', 'c']]);
        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertCount(3, (array) $result);
        $this->assertSame('Hallo Welt!', $result->title);
        $this->assertSame(['a', 'b', 'c'], $result->content);
    }

    public function testGetArrayRowByFieldValue()
    {
        $arrayUtil = new ArrayUtil($this->getContainerMock());
        $this->assertSame(['id' => 5, 'hallo' => 'welt5'], $arrayUtil->getArrayRowByFieldValue('id', 5, [
            ['id' => 1, 'hallo' => 'welt'],
            ['id' => 5, 'hallo' => 'welt5'],
        ]));
        $this->assertSame(['id' => 5, 'hallo' => 'welt5'], $arrayUtil->getArrayRowByFieldValue('id', 5, [
            ['id' => 1, 'hallo' => 'welt'],
            'id' => 5,
            ['id' => 5, 'hallo' => 'welt5'],
        ]));
        $this->assertSame(['id' => 5, 'hallo' => 'welt5'], $arrayUtil->getArrayRowByFieldValue('id', 5, [
            ['id' => 1, 'hallo' => 'welt'],
            ['pid' => 2, 'hallo' => 'sonnensystem2'],
            'id' => 5,
            ['id' => 5, 'hallo' => 'welt5'],
        ]));
        $this->assertSame(['id' => '5', 'hallo' => 'welt5'], $arrayUtil->getArrayRowByFieldValue('id', 5, [
            ['id' => 1, 'hallo' => 'welt'],
            'id' => 5,
            ['id' => '5', 'hallo' => 'welt5'],
        ]));
        $this->assertFalse($arrayUtil->getArrayRowByFieldValue('id', 5, [
            ['id' => 1, 'hallo' => 'welt'],
            'id' => 5,
            ['id' => '5', 'hallo' => 'welt5'],
        ], true));
        $this->assertFalse($arrayUtil->getArrayRowByFieldValue('id', 5, [
            ['id' => 1, 'hallo' => 'welt'],
            ['id' => 4, 'hallo' => 'welt4'],
        ]));
        $this->assertFalse($arrayUtil->getArrayRowByFieldValue('id', 5, ['a', 'b']));
    }

    public function testFlattenArray()
    {
        $arrayUtil = new ArrayUtil($this->getContainerMock());
        $this->assertSame(['hallo'], $arrayUtil->flattenArray([1 => 'hallo']));
        $this->assertSame(['hallo'], $arrayUtil->flattenArray([1 => ['hallo']]));
        $this->assertSame(['hallo'], $arrayUtil->flattenArray([1 => ['hallo']]));
        $this->assertSame(['hallo', 'welt'], $arrayUtil->flattenArray([
            1 => ['hallo', 'welt'],
        ]));
        $this->assertSame(['hallo', 'schöne', 'welt'], $arrayUtil->flattenArray([
            1 => [
                'hallo',
                ['schöne'],
                'welt', ],
        ]));
        $this->assertSame(['hallo', 'schöne', 'kleine', 'welt', '!'], $arrayUtil->flattenArray([
            1 => [
                'hallo',
                ],
            ['schöne'],
            3 => 'kleine',
            4 => [
                'welt',
                ['satzzeichen' => '!'],
            ],
        ]));
    }

    public function testInsertBeforeKey()
    {
        $current = ['hello' => 'world'];
        ArrayUtil::insertBeforeKey($current, 'hello', 'foo', 'bar');
        $this->assertSame(['foo' => 'bar', 'hello' => 'world'], $current);

        $current = ['hello' => 'world'];
        ArrayUtil::insertBeforeKey($current, 'tux', 'foo', 'bar');
        $this->assertSame(['hello' => 'world', 'foo' => 'bar'], $current);
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

        try {
            /** @noinspection PhpParamsInspection */
            $stringUtil = new StringUtil($container->get('contao.framework'));
        } catch (\Exception $e) {
            $this->fail('Could net get service from container. Message: '.$e->getMessage());
        }
        $container->set('huh.utils.string', $stringUtil);

        return $container;
    }
}

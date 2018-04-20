<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Arrays;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Arrays\ArrayUtil;
use HeimrichHannot\UtilsBundle\String\StringUtil;

class ArrayUtilTest extends ContaoTestCase
{
    public function setUp()
    {
        parent::setUp();

        $stringUtil = new StringUtil($this->mockContaoFramework());

        $container = $this->mockContainer();
        $container->set('huh.utils.string', $stringUtil);
        System::setContainer($container);
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $framework = $this->mockContaoFramework();
        $instance = new ArrayUtil($framework);
        $this->assertInstanceOf(ArrayUtil::class, $instance);
    }

    public function testAasort()
    {
        $framework = $this->mockContaoFramework();
        $arrayUtil = new ArrayUtil($framework);

        $array = [0 => ['filename' => 'testfile3'], 1 => ['filename' => 'testfile1'], 2 => ['filename' => 'testfile2']];

        $arrayUtil->aasort($array, 'filename');
        $this->assertSame([1 => ['filename' => 'testfile1'], 2 => ['filename' => 'testfile2'], 0 => ['filename' => 'testfile3']], $array);
    }

    public function testRemoveValue()
    {
        $framework = $this->mockContaoFramework();
        $arrayUtil = new ArrayUtil($framework);

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
        $framework = $this->mockContaoFramework();
        $arrayUtil = new ArrayUtil($framework);

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
        $framework = $this->mockContaoFramework();
        $arrayUtil = new ArrayUtil($framework);

        $array = ['ls_prefix_1' => 1];
        $result = $arrayUtil->removePrefix('ls_', $array);
        $this->assertSame(['prefix_1' => 1], $result);
    }
}

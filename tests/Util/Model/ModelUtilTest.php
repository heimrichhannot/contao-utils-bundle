<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Util\Model;

use HeimrichHannot\UtilsBundle\Tests\AbstractUtilsTestCase;
use HeimrichHannot\UtilsBundle\Util\Model\ModelUtil;
use PHPUnit\Framework\MockObject\MockBuilder;

class ModelUtilTest extends AbstractUtilsTestCase
{
    public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null)
    {
        return new ModelUtil();
    }

    /**
     * @runInSeparateProcess
     */
    public function testAddPublishedCheckToModelArrays()
    {
        $instance = $this->getTestInstance();

        $columns = [];
        $instance->addPublishedCheckToModelArrays('tl_test', $columns);
        $this->assertCount(1, $columns);
        $this->assertStringStartsWith("(tl_test.start=''", $columns[0]);
        $this->assertStringEndsWith("tl_test.published='1'", $columns[0]);

        $columns = [];
        $instance->addPublishedCheckToModelArrays('tl_test', $columns, [
            'publishedField' => 'visible',
            'startField' => 'show',
            'stopField' => 'hide',
        ]);
        $this->assertStringStartsWith("(tl_test.show=''", $columns[0]);
        $this->assertStringEndsWith("tl_test.visible='1'", $columns[0]);
        $this->assertTrue(false !== strpos($columns[0], "tl_test.hide=''"));
        $this->assertTrue(false !== strpos($columns[0], "tl_test.show<='"));
        $this->assertTrue(false !== strpos($columns[0], "tl_test.hide>'"));

        $columns = [
            'tl_text.field=?',
        ];
        $instance->addPublishedCheckToModelArrays('tl_test', $columns, [
            'invertPublishedField' => true,
            'publishedField' => 'hidden',
        ]);
        $this->assertCount(2, $columns);
        $this->assertStringEndsWith("tl_test.hidden!='1'", $columns[1]);

        $columns = [];
        $instance->addPublishedCheckToModelArrays('tl_test', $columns, ['invertStartStopFields' => true]);
        $this->assertStringStartsWith("(tl_test.start!=''", $columns[0]);
        $this->assertTrue(false !== strpos($columns[0], "tl_test.stop!=''"));
        $this->assertTrue(false !== strpos($columns[0], "tl_test.start>'"));
        $this->assertTrue(false !== strpos($columns[0], "tl_test.stop<='"));

        \define('BE_USER_LOGGED_IN', true);
        $columns = [];
        $instance->addPublishedCheckToModelArrays('tl_test', $columns);
        $this->assertEmpty($columns);
    }
}

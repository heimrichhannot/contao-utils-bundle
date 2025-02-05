<?php

namespace HeimrichHannot\UtilsBundle\Tests\Dca;


use HeimrichHannot\UtilsBundle\Dca\DcaFieldConfiguration;
use PHPUnit\Framework\TestCase;

class DcaFieldOptionsTest extends TestCase
{
    public function testAllOptions()
    {
        $dcaFieldOptions = new DcaFieldConfiguration('test_table');
        $this->assertEquals('test_table', $dcaFieldOptions->getTable());

        $dcaFieldOptions
            ->setFlag(69)
            ->setExclude(true)
            ->setSearch(true)
            ->setFilter(true)
            ->setSorting(true)
            ->setEvalValue('test_eval_1', 'test_value_1')
            ->setEvalValue('test_eval_2', 'test_value_2');
        $this->assertEquals(69, $dcaFieldOptions->getFlag());
        $this->assertTrue($dcaFieldOptions->isExclude());
        $this->assertTrue($dcaFieldOptions->isSearch());
        $this->assertTrue($dcaFieldOptions->isFilter());
        $this->assertTrue($dcaFieldOptions->isSorting());
        $this->assertEquals(
            [
                'test_eval_1' => 'test_value_1',
                'test_eval_2' => 'test_value_2',
            ],
            $dcaFieldOptions->getEval()
        );

        $dcaFieldOptions->setEvalValue('test_eval_2', 'test_value_new2');
        $this->assertEquals(
            [
                'test_eval_1' => 'test_value_1',
                'test_eval_2' => 'test_value_new2',
            ],
            $dcaFieldOptions->getEval()
        );

        $dcaFieldOptions->setEvalValue('test_eval_1', 'test_value_single1');
        $dcaFieldOptions->setEvalValue('test_eval_2', 'test_value_single2');
        $dcaFieldOptions->setEvalValue('test_eval_3', 'test_value_single3');
        $this->assertEquals('test_value_single1', $dcaFieldOptions->getEvalValue('test_eval_1'));
        $this->assertEquals('test_value_single2', $dcaFieldOptions->getEvalValue('test_eval_2'));
        $this->assertEquals('test_value_single3', $dcaFieldOptions->getEvalValue('test_eval_3'));
    }
}
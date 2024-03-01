<?php

namespace HeimrichHannot\UtilsBundle\Tests\Dca;


use HeimrichHannot\UtilsBundle\Dca\DcaFieldOptions;
use PHPUnit\Framework\TestCase;

class DcaFieldOptionsTest extends TestCase
{
    public function testGetTable()
    {
        $dcaFieldOptions = new DcaFieldOptions('test_table');
        $this->assertEquals('test_table', $dcaFieldOptions->getTable());
    }
}
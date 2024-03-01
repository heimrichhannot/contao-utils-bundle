<?php

namespace HeimrichHannot\UtilsBundle\Tests\Dca;


use HeimrichHannot\UtilsBundle\Dca\DcaFieldConfiguration;
use PHPUnit\Framework\TestCase;

class DcaFieldOptionsTest extends TestCase
{
    public function testGetTable()
    {
        $dcaFieldOptions = new DcaFieldConfiguration('test_table');
        $this->assertEquals('test_table', $dcaFieldOptions->getTable());
    }
}
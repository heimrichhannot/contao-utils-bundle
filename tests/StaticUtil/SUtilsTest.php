<?php

namespace StaticUtil;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\StaticUtil\StaticArrayUtil;
use HeimrichHannot\UtilsBundle\StaticUtil\StaticClassUtil;
use HeimrichHannot\UtilsBundle\StaticUtil\SUtils;

class SUtilsTest extends ContaoTestCase
{
    public function testSUtils()
    {
        $this->assertInstanceOf(StaticArrayUtil::class, SUtils::array());
        $this->assertInstanceOf(StaticClassUtil::class, SUtils::class());
    }
}
<?php

namespace HeimrichHannot\UtilsBundle\Tests\Util\Data;

use HeimrichHannot\UtilsBundle\Tests\AbstractUtilsTestCase;
use HeimrichHannot\UtilsBundle\Util\Data\AnonymizeUtil;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\TestCase;

class AnonymizeUtilTest extends AbstractUtilsTestCase
{

    public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null)
    {
        return new AnonymizeUtil();
    }

    public function testAnonymizeEmail()
    {
        $instance = $this->getTestInstance();
        $this->assertSame('max.mus*******@example.org', $instance->anonymizeEmail('max.mustermann@example.org'));
        $this->assertSame('digi****@heimrich-hannot.de', $instance->anonymizeEmail('digitales@heimrich-hannot.de'));
        $this->assertSame('dasIstKeinE-Mail', $instance->anonymizeEmail('dasIstKeinE-Mail'));
    }
}

<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\UtilsBundle\Tests\String;


use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\String\AnonymizerUtil;

class AnonymizerUtilTest extends ContaoTestCase
{
    public function getInstance()
    {
        return new AnonymizerUtil();
    }

    public function testAnonymizeEmail()
    {
        $instance = $this->getInstance();
        $this->assertSame('max.mus*******@example.org', $instance->anonymizeEmail('max.mustermann@example.org'));
        $this->assertSame('digi****@heimrich-hannot.de', $instance->anonymizeEmail('digitales@heimrich-hannot.de'));
        $this->assertSame('dasIstKeinE-Mail', $instance->anonymizeEmail('dasIstKeinE-Mail'));

    }
}
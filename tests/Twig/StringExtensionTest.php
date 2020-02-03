<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\UtilsBundle\Tests\Twig;


use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\String\AnonymizerUtil;
use HeimrichHannot\UtilsBundle\Twig\StringExtension;
use Twig\TwigFilter;

class StringExtensionTest extends ContaoTestCase
{
    public function createInstance()
    {
        $anonymizerUtil = new AnonymizerUtil();
        $instance = new StringExtension($anonymizerUtil);
        return $instance;
    }

    public function testGetFilters()
    {
        $instance = $this->createInstance();
        $filters = $instance->getFilters();
        $this->assertInstanceOf(TwigFilter::class, $filters[0]);
    }
    
    public function testAnonymizeEmail()
    {
        $instance = $this->createInstance();
        $this->assertSame('max.mus*******@example.org', $instance->anonymizeEmail('max.mustermann@example.org'));
        $this->assertSame('digi****@heimrich-hannot.de', $instance->anonymizeEmail('digitales@heimrich-hannot.de'));
        $this->assertSame('dasIstKeinE-Mail', $instance->anonymizeEmail('dasIstKeinE-Mail'));
    }
}
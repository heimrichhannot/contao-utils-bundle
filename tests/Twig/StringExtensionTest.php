<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Twig;

use Contao\Controller;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\String\AnonymizerUtil;
use HeimrichHannot\UtilsBundle\Twig\StringExtension;
use Twig\TwigFilter;

class StringExtensionTest extends ContaoTestCase
{
    public function createInstance($parameter = [])
    {
        if (!isset($parameter['framework'])) {
            $parameter['framework'] = $this->mockContaoFramework();
        }
        $anonymizerUtil = new AnonymizerUtil();
        $instance = new StringExtension($anonymizerUtil, $parameter['framework']);

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

    public function testReplaceInsertTag()
    {
        $controller = $this->mockAdapter(['replaceInsertTags']);
        $controller->expects($this->once())->method('replaceInsertTags')->willReturnArgument(0);
        $framework = $this->mockContaoFramework([
           Controller::class => $controller,
        ]);
        $framework->expects($this->once())->method('initialize');
        $instance = $this->createInstance(['framework' => $framework]);
        $this->assertSame('No inserttag', $instance->replaceInsertTag('No inserttag'));
    }
}

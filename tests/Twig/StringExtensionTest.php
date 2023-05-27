<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Twig;

use Contao\Controller;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\String\AnonymizerUtil;
use HeimrichHannot\UtilsBundle\Tests\AbstractUtilsTestCase;
use HeimrichHannot\UtilsBundle\Twig\StringExtension;
use HeimrichHannot\UtilsBundle\Util\Data\AnonymizeUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use PHPUnit\Framework\MockObject\MockBuilder;
use Twig\TwigFilter;

class StringExtensionTest extends AbstractUtilsTestCase
{
    public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null)
    {
        $utilsMock = $parameters['utils'] ?? $this->createMock(Utils::class);
        return new StringExtension($utilsMock);
    }

    public function testGetFilters()
    {
        $instance = $this->getTestInstance();
        $this->assertCount(2, $instance->getFilters());
        $this->assertInstanceOf(TwigFilter::class, $instance->getFilters()[0]);
    }

    public function testAnonymizeEmail()
    {
        $anonymizeUtil = $this->createMock(AnonymizeUtil::class);
        $anonymizeUtil->expects($this->once())->method('anonymizeEmail')->willReturn('');

        $utils = $this->createMock(Utils::class);
        $utils->method('anonymize')->willReturn($anonymizeUtil);

        $instance = $this->getTestInstance(['utils' => $utils]);
        $instance->anonymizeEmail('max.mustermann@example.org');
    }
}

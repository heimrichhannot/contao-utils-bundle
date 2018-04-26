<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Template;

use Contao\CoreBundle\Config\ResourceFinder;
use Contao\System;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Template\TemplateUtil;
use HeimrichHannot\UtilsBundle\Tests\TestCaseEnvironment;

class TemplateUtilTest extends TestCaseEnvironment
{
    public function testInstantiation()
    {
        $util = new TemplateUtil($this->mockContaoFramework());
        $this->assertInstanceOf(TemplateUtil::class, $util);
    }

    public function testGetTwigTemplate()
    {
        if (!defined('TL_MODE')) {
            \define('TL_MODE', 'FE');
        }

        $finder = new ResourceFinder(([
            $this->getFixturesDir(),
        ]));

        $container = System::getContainer();
        $container->set('contao.resource_finder', $finder);

        $containerUtil = new ContainerUtil($this->mockContaoFramework());
        $container->set('huh.utils.container', $containerUtil);

        System::setContainer($container);

        global $objPage;

        $objPage = new \stdClass();
        $objPage->templateGroup = '';

        $util = new TemplateUtil($this->mockContaoFramework());
        $this->assertSame($this->getFixturesDir().'/templates/test.html.twig', $util->getTemplate('test'));
    }

    public function testGetTwigTemplateInThemePath()
    {
        if (!defined('TL_MODE')) {
            \define('TL_MODE', 'FE');
        }

        $finder = new ResourceFinder(([
            $this->getFixturesDir(),
        ]));

        $container = System::getContainer();
        $container->set('contao.resource_finder', $finder);

        $containerUtil = new ContainerUtil($this->mockContaoFramework());
        $container->set('huh.utils.container', $containerUtil);

        System::setContainer($container);

        global $objPage;

        $objPage = new \stdClass();
        $objPage->templateGroup = 'myTheme';

        if (!defined('TL_ROOT')) {
            define('TL_ROOT', $this->getFixturesDir());
        }

        $util = new TemplateUtil($this->mockContaoFramework());
        $this->assertSame($this->getFixturesDir().'/templates/myTheme/test1.html.twig', $util->getTemplate('test1'));
    }
}

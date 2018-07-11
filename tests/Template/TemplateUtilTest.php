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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Config\FileLocator;

class TemplateUtilTest extends TestCaseEnvironment
{
    public function setUp()
    {
        parent::setUp();

        $fs = new Filesystem();
        $fs->mkdir($this->getTempDir());

        $container = $this->mockContainer();

        $file1 = $this->createMock(\SplFileInfo::class);
        $file1->method('getBasename')->willReturn('basename');

        $file2 = $this->createMock(\SplFileInfo::class);
        $file2->method('getBasename')->willReturn('basename');

        $finder = $this->mockAdapter(['findIn', 'name']);
        $finder->method('findIn')->willReturnSelf();
        $finder->method('name')->willReturn([$file1, $file2]);

        $container->set('contao.resource_finder', $finder);

        $kernel = $this->mockAdapter(['getCacheDir', 'isDebug']);
        $kernel->method('getCacheDir')->willReturn($this->getTempDir());
        $kernel->method('isDebug')->willReturn(false);
        $container->setParameter('kernel.debug', true);
        $container->set('kernel', $kernel);
        System::setContainer($container);
    }

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

        $containerUtil = new ContainerUtil($this->mockContaoFramework(), $this->createMock(FileLocator::class));
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

        $containerUtil = new ContainerUtil($this->mockContaoFramework(), $this->createMock(FileLocator::class));
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

    public function testRemoveTemplateComment()
    {
        $util = new TemplateUtil($this->mockContaoFramework());

        $this->assertNull($util->removeTemplateComment(null));
        $this->assertSame('', $util->removeTemplateComment('<!-- TEMPLATE START: system/modules/blocks/templates/modules/mod_block.html5 -->
        <!-- TEMPLATE END: system/modules/blocks/templates/modules/mod_block.html5 -->'));
    }
}

<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Template;

use Contao\CoreBundle\Config\ResourceFinder;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Template\TemplateUtil;
use HeimrichHannot\UtilsBundle\Tests\FixturesTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;

class TemplateUtilTest extends ContaoTestCase
{
    use FixturesTrait;

    public function setUp()
    {
        parent::setUp();

        $fs = new Filesystem();
        $fs->mkdir($this->getTempDir());
    }

    /**
     * @return TemplateUtil
     */
    public function getTemplateUtilMock(ContainerInterface $container = null)
    {
        $util = new TemplateUtil($this->getContainerMock());

        return $util;
    }

    public function testInstantiation()
    {
        $util = $this->getTemplateUtilMock();
        $this->assertInstanceOf(TemplateUtil::class, $util);
    }

    public function testGetTwigTemplate()
    {
        $container = $this->getContainerMock();
        $util = new TemplateUtil($container);
        $util->getAllTemplates();

        if (!\defined('TL_MODE')) {
            \define('TL_MODE', 'FE');
        }

        $finder = new ResourceFinder(
            ([
                $this->getFixturesDir(),
            ])
        );

        global $objPage;

        $objPage = new \stdClass();
        $objPage->templateGroup = '';

        $this->assertSame($this->getFixturesDir().'/templates/test.html.twig', $util->getTemplate('test'));
    }

    public function testGetTwigTemplateInThemePath()
    {
        if (!\defined('TL_MODE')) {
            \define('TL_MODE', 'FE');
        }

        $container = $this->getContainerMock();

        $containerUtil = $this->createMock(ContainerUtil::class);
        $containerUtil->method('getProjectDir')->willReturn($this->getFixturesDir());
        $containerUtil->method('isFrontend')->willReturn(true);
        $container->set('huh.utils.container', $containerUtil);

        $util = new TemplateUtil($container);
        $util->getAllTemplates();

        global $objPage;
        $objPage = new \stdClass();
        $objPage->templateGroup = 'templates/myTheme';

        $this->assertSame($this->getFixturesDir().'/templates/myTheme/test1.html.twig', $util->getTemplate('test1'));
    }

    public function testRemoveTemplateComment()
    {
        $container = $this->getContainerMock();
        $util = new TemplateUtil($container);
        System::setContainer($container);

        $this->assertEmpty($util->removeTemplateComment(null));
        $this->assertSame(
            '',
            $util->removeTemplateComment(
                '<!-- TEMPLATE START: system/modules/blocks/templates/modules/mod_block.html5 -->
        <!-- TEMPLATE END: system/modules/blocks/templates/modules/mod_block.html5 -->'
            )
        );
    }

    public function testIsTemplatePartEmpty()
    {
        $util = $this->getTemplateUtilMock();

        $this->assertTrue($util->isTemplatePartEmpty('    '));
        $this->assertTrue(
            $util->isTemplatePartEmpty(
                '<!-- TEMPLATE START: system/modules/blocks/templates/modules/mod_block.html5 -->



<!-- TEMPLATE END: system/modules/blocks/templates/modules/mod_block.html5 -->'
            )
        );
        $this->assertFalse($util->isTemplatePartEmpty('<!-- TEMPLATE START: system/modules/blocks/templates/modules/mod_block.html5 --><div class="my_block"></div><!-- TEMPLATE END: system/modules/blocks/templates/modules/mod_block.html5 -->'));
        $this->assertTrue($util->isTemplatePartEmpty(null));
    }

    protected function getContainerMock(ContainerBuilder $container = null)
    {
        if (!$container) {
            $container = $this->mockContainer();
        }

        if (!$container->has('kernel')) {
            $kernel = $this->createMock(KernelInterface::class);
            $kernel->method('getCacheDir')->willReturn($this->getTempDir());
            $kernel->method('isDebug')->willReturn(false);
            $container->setParameter('kernel.debug', true);
            $container->set('kernel', $kernel);
        }

        $container->set('contao.resource_finder', new ResourceFinder([$this->getFixturesDir()]));

        if (!$container->has('request_stack')) {
            $request = new Request();
            $requestStack = $this->createMock(RequestStack::class);
            $requestStack->method('getCurrentRequest')->willReturn($request);
            $container->set('request_stack', $requestStack);
        }

        $container->setParameter('kernel.project_dir', $this->getFixturesDir());

        if (!$container->has('huh.utils.container')) {
            $containerUtil = $this->createMock(ContainerUtil::class);
            $containerUtil->method('getProjectDir')->willReturn($this->getFixturesDir());
            $container->set('huh.utils.container', $containerUtil);
        }

        return $container;
    }
}

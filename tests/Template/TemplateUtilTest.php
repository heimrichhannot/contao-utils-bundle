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
use HeimrichHannot\UtilsBundle\Template\TemplateLocator;
use HeimrichHannot\UtilsBundle\Template\TemplateUtil;
use HeimrichHannot\UtilsBundle\Tests\FixturesTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

        if (!\defined('TL_ROOT')) {
            \define('TL_ROOT', $this->getTempDir());
        }
    }

    /**
     * @return TemplateUtil
     */
    public function createTestInstance(array $parameters = [])
    {
        if (!isset($parameters['container'])) {
            $parameters['container'] = $this->getContainerMock();
        }

        if (!isset($parameters['templateLocator'])) {
            $parameters['templateLocator'] = $this->createMock(TemplateLocator::class);
        }
        $util = new TemplateUtil($parameters['container'], $parameters['templateLocator']);
        System::setContainer($parameters['container']);

        return $util;
    }

    public function testInstantiation()
    {
        $util = $this->createTestInstance();
        $this->assertInstanceOf(TemplateUtil::class, $util);
    }

    public function testGetTwigTemplate()
    {
        $templateLocator = $this->createMock(TemplateLocator::class);
        $templateLocator->expects($this->once())->method('getAllTemplates')->willReturn(['template' => 'templates/template.html.twig']);
        $util = $this->createTestInstance(['templateLocator' => $templateLocator]);
        $templates = $util->getAllTemplates();

        $this->assertCount(1, $templates);
        $this->assertArrayHasKey('template', $templates);

        $util->getAllTemplates();
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

        $util = $this->createTestInstance(['container' => $container]);

        $util->getAllTemplates();

        global $objPage;
        $objPage = new \stdClass();
        $objPage->templateGroup = 'templates/myTheme';

        $this->assertSame($this->getFixturesDir().'/templates/myTheme/test1.html.twig', $util->getTemplate('test1'));
    }

    public function testRemoveTemplateComment()
    {
        $util = $this->createTestInstance();

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
        $util = $this->createTestInstance();

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

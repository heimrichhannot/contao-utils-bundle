<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Template;

use Contao\CoreBundle\Config\ResourceFinder;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\System;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Template\TemplateUtil;
use HeimrichHannot\UtilsBundle\Tests\TestCaseEnvironment;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\HttpKernel\KernelInterface;

class TemplateUtilTest extends TestCaseEnvironment
{
    public function setUp()
    {
        parent::setUp();

        $fs = new Filesystem();
        $fs->mkdir($this->getTempDir());
    }

    /**
     * @param ContainerInterface|null $container
     *
     * @return TemplateUtil
     */
    public function getTemplateUtilMock(ContainerInterface $container = null, ContaoFrameworkInterface $framework = null)
    {
        if (!$framework) {
            $framework = $this->mockContaoFramework();
        }

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

        if (!$container->has('contao.resource_finder')) {
            $file1 = $this->createMock(\SplFileInfo::class);
            $file1->method('getBasename')->willReturn('basename');

            $file2 = $this->createMock(\SplFileInfo::class);
            $file2->method('getBasename')->willReturn('basename');

            $finder = $this->mockAdapter(['findIn', 'name']);
            $finder->method('findIn')->willReturnSelf();
            $finder->method('name')->willReturn([$file1, $file2]);

            $container->set('contao.resource_finder', $finder);
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

        System::setContainer($container);
        $util = new TemplateUtil($framework, $kernel, $container);

        return $util;
    }

    public function testInstantiation()
    {
        $util = $this->getTemplateUtilMock();
        $this->assertInstanceOf(TemplateUtil::class, $util);
    }

    public function testGetTwigTemplate()
    {
        $util = $this->getTemplateUtilMock();
        $util->getAllTemplates();

        if (!\defined('TL_MODE')) {
            \define('TL_MODE', 'FE');
        }

        $finder = new ResourceFinder(
            ([
                $this->getFixturesDir(),
            ])
        );

        $container = System::getContainer();
        $container->set('contao.resource_finder', $finder);

        $containerUtil = new ContainerUtil($this->mockContaoFramework(), $this->createMock(FileLocator::class), $this->createMock(ScopeMatcher::class));
        $container->set('huh.utils.container', $containerUtil);

        System::setContainer($container);

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

        $finder = new ResourceFinder(
            ([
                $this->getFixturesDir(),
            ])
        );

        $container = System::getContainer();
        $container->set('contao.resource_finder', $finder);

        $container->setParameter('kernel.project_dir', $this->getFixturesDir());
        $containerUtil = $this->createMock(ContainerUtil::class);
        $containerUtil->method('getProjectDir')->willReturn($this->getFixturesDir());
        $containerUtil->method('isFrontend')->willReturn(true);
        $container->set('huh.utils.container', $containerUtil);

        $util = $this->getTemplateUtilMock($container);
        $util->getAllTemplates();

        System::setContainer($container);

        global $objPage;

        $objPage = new \stdClass();
        $objPage->templateGroup = 'templates/myTheme';

        $this->assertSame($this->getFixturesDir().'/templates/myTheme/test1.html.twig', $util->getTemplate('test1'));
    }

    public function testRemoveTemplateComment()
    {
        $util = $this->getTemplateUtilMock();

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
}

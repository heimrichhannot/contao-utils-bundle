<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Choice;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Arrays\ArrayUtil;
use HeimrichHannot\UtilsBundle\Choice\TwigTemplateChoice;
use HeimrichHannot\UtilsBundle\HeimrichHannotContaoUtilsBundle;
use HeimrichHannot\UtilsBundle\String\StringUtil;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;

class TwigTemplateChoiceTest extends ContaoTestCase
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    public function setUp()
    {
        parent::setUp();

        $fs = new Filesystem();
        $fs->mkdir($this->getTempDir());

        $this->container = $this->mockContainer();
        $this->container->set('contao.framework', $this->mockContaoFramework());

        $this->container->set('huh.utils.array', new ArrayUtil($this->container));
        $this->container->set('huh.utils.string', new StringUtil($this->mockContaoFramework()));

        $translator = $this->mockAdapter(['getCatalogue', 'all']);
        $translator->method('getCatalogue')->willReturnSelf();
        $translator->method('all')->willReturn(['messages' => 'all']);
        $this->container->set('translator', $translator);
        $this->container->setParameter('kernel.debug', true);
    }

    /**
     * Test collect().
     */
    public function testCollect()
    {
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\KernelInterface')->getMock();
        $kernel
            ->expects($this->once())
            ->method('getBundles')
            ->willReturn(['HeimrichHannotContaoUtilsBundle' => new HeimrichHannotContaoUtilsBundle()]);

        $kernel->method('locateResource')
            ->willReturn(__DIR__.'/../../src');

        $this->container->set('kernel', $kernel);

        System::setContainer($this->container);

        $choice = new TwigTemplateChoice($this->mockContaoFramework());
        $choices = $choice->getChoices();
        $this->assertNotEmpty($choices);
        $this->assertContains('@HeimrichHannotContaoUtils/image.html.twig', $choices);
        $this->assertContains('@HeimrichHannotContaoUtils/picture.html.twig', $choices);
    }

    /**
     * Test collect() by prefixes.
     */
    public function testCollectByPrefixes()
    {
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\KernelInterface')->getMock();
        $kernel
            ->expects($this->once())
            ->method('getBundles')
            ->willReturn(['HeimrichHannotContaoUtilsBundle' => new HeimrichHannotContaoUtilsBundle()]);

        $kernel->method('locateResource')
            ->willReturn(__DIR__.'/../../src');

        $this->container->set('kernel', $kernel);

        System::setContainer($this->container);

        $choice = new TwigTemplateChoice($this->mockContaoFramework());
        $choices = $choice->getChoices(['image']);
        $this->assertNotEmpty($choices);

        $this->assertContains('@HeimrichHannotContaoUtils/image.html.twig', $choices);
    }

    /**
     * Test collect() by prefixes.
     */
    public function testCollectByMultiplePrefixes()
    {
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\KernelInterface')->getMock();
        $kernel
            ->expects($this->once())
            ->method('getBundles')
            ->willReturn(['HeimrichHannotContaoUtilsBundle' => new HeimrichHannotContaoUtilsBundle()]);

        $kernel->method('locateResource')
            ->willReturn(__DIR__.'/../../src');

        $this->container->set('kernel', $kernel);

        System::setContainer($this->container);

        $choice = new TwigTemplateChoice($this->mockContaoFramework());
        $choices = $choice->getChoices(['image', 'picture']);
        $this->assertNotEmpty($choices);

        $this->assertContains('@HeimrichHannotContaoUtils/image.html.twig', $choices);
        $this->assertContains('@HeimrichHannotContaoUtils/picture.html.twig', $choices);
    }

    /**
     * Test collect() by prefixes without match.
     */
    public function testCollectByPrefixesWithoutMatch()
    {
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\KernelInterface')->getMock();
        $kernel
            ->expects($this->once())
            ->method('getBundles')
            ->willReturn(['HeimrichHannotContaoUtilsBundle' => new HeimrichHannotContaoUtilsBundle()]);

        $kernel->method('locateResource')
            ->willReturn(__DIR__.'/../../src');

        $this->container->set('kernel', $kernel);

        System::setContainer($this->container);

        $choice = new TwigTemplateChoice($this->mockContaoFramework());
        $choices = $choice->getChoices(['imageXYZ!"§']);
        $this->assertEmpty($choices);
    }

    /**
     * Test collect() by prefixes with suffix.
     */
    public function testCollectByPrefixesWithSuffix()
    {
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\KernelInterface')->getMock();
        $kernel
            ->expects($this->once())
            ->method('getBundles')
            ->willReturn(['HeimrichHannotContaoUtilsBundle' => new HeimrichHannotContaoUtilsBundle()]);

        $kernel->method('locateResource')
            ->willReturn(__DIR__.'/../../src');

        $this->container->set('kernel', $kernel);

        System::setContainer($this->container);

        $choice = new TwigTemplateChoice($this->mockContaoFramework());
        $choices = $choice->getChoices(['image.html']);
        $this->assertNotEmpty($choices);

        $this->assertContains('@HeimrichHannotContaoUtils/image.html.twig', $choices);
    }
}

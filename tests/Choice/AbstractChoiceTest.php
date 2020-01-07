<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Choice;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;
use HeimrichHannot\UtilsBundle\Choice\FieldChoice;
use Symfony\Component\Filesystem\Filesystem;

class AbstractChoiceTest extends ContaoTestCase
{
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    public function setUp()
    {
        parent::setUp();

        $fs = new Filesystem();
        $fs->mkdir($this->getTempDir());

        $container = $this->mockContainer();
        $dcaAdapter = $this->mockAdapter(['getFields']);
        $dcaAdapter->method('getFields')->willReturn(['success']);
        $container->set('huh.utils.dca', $dcaAdapter);

        $kernel = $this->mockAdapter(['getCacheDir', 'isDebug']);
        $kernel->method('getCacheDir')->willReturn($this->getTempDir());
        $kernel->method('isDebug')->willReturn(false);
        $container->set('kernel', $kernel);
        $container->setParameter('kernel.debug', true);
        System::setContainer($container);
    }

    public function testCanBeInstantiated()
    {
        $fieldChoice = new FieldChoice($this->mockContaoFramework());
        $this->assertInstanceOf(AbstractChoice::class, $fieldChoice);
    }

    public function testContext()
    {
        $fieldChoice = new FieldChoice($this->mockContaoFramework());
        $fieldChoice->setContext(['context']);
        $context = $fieldChoice->getContext();
        $this->assertSame(['context'], $context);
    }

    public function testGetChoices()
    {
        $fieldChoice = new FieldChoice($this->mockContaoFramework());
        $context = $fieldChoice->getChoices();
        $this->assertSame(['success'], $context);
    }

    public function testGetCachedChoices()
    {
        $container = System::getContainer();
        $dcaAdapter = $this->mockAdapter(['getFields']);
        $dcaAdapter->method('getFields')->willReturn('success');
        $container->set('huh.utils.dca', $dcaAdapter);
        $container->setParameter('kernel.debug', false);
        System::setContainer($container);

        $fieldChoice = new FieldChoice($this->mockContaoFramework());
        $cachedChoices = $fieldChoice->getCachedChoices(['dataContainer' => 'success']);
        $this->assertSame([], $cachedChoices);

        $container = System::getContainer();
        $kernel = $this->mockAdapter(['getCacheDir', 'isDebug']);
        $kernel->method('getCacheDir')->willReturn($this->getTempDir());
        $kernel->method('isDebug')->willReturn(false);
        $container->setParameter('kernel.debug', true);
        $container->set('kernel', $kernel);
        System::setContainer($container);

        $fieldChoice = new FieldChoice($this->mockContaoFramework());
        $cachedChoices = $fieldChoice->getCachedChoices(['dataContainer' => 'success']);
        $this->assertSame('success', $cachedChoices);
    }
}

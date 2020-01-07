<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Choice;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Choice\DataContainerChoice;
use Symfony\Component\Filesystem\Filesystem;

class DataContainerChoiceTest extends ContaoTestCase
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

    public function testCollect()
    {
        $choice = new DataContainerChoice($this->mockContaoFramework());
        $choices = $choice->getChoices();
        $this->assertSame(['basename'], $choices);

        $container = System::getContainer();
        $finder = $this->mockAdapter(['findIn', 'name']);
        $finder->method('findIn')->willReturnSelf();
        $finder->method('name')->willThrowException(new \InvalidArgumentException());
        $container->set('contao.resource_finder', $finder);
        System::setContainer($container);

        $choices = $choice->getChoices();
        $this->assertSame([], $choices);
    }
}

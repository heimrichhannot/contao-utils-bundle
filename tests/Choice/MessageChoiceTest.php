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
use HeimrichHannot\UtilsBundle\Choice\MessageChoice;
use Symfony\Component\Filesystem\Filesystem;

class MessageChoiceTest extends ContaoTestCase
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

        $kernel = $this->mockAdapter(['getCacheDir', 'isDebug']);
        $kernel->method('getCacheDir')->willReturn($this->getTempDir());
        $kernel->method('isDebug')->willReturn(false);
        $container->set('kernel', $kernel);

        $translator = $this->mockAdapter(['getCatalogue', 'all']);
        $translator->method('getCatalogue')->willReturnSelf();
        $translator->method('all')->willReturn(['messages' => 'all']);
        $container->set('translator', $translator);
        $container->setParameter('kernel.debug', true);

        System::setContainer($container);
    }

    public function testCollect()
    {
        $choice = new MessageChoice($this->mockContaoFramework());
        $choices = $choice->getChoices();
        $this->assertSame([], $choices);

        $container = System::getContainer();
        $translator = $this->mockAdapter(['getCatalogue', 'all']);
        $translator->method('getCatalogue')->willReturnSelf();
        $translator->method('all')->willReturn(['messages' => ['all' => '41']]);
        $container->set('translator', $translator);

        $arrayUtil = $this->createMock(ArrayUtil::class);
        $arrayUtil->method('filterByPrefixes')->willReturn(['all' => '41']);
        $container->set('huh.utils.array', $arrayUtil);
        $container->set('contao.framework', $this->mockContaoFramework());
        System::setContainer($container);

        $choice = new MessageChoice($this->mockContaoFramework());
        $choices = $choice->getChoices();
        $this->assertSame(['all' => '41[all]'], $choices);
    }
}

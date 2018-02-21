<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Choice;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Choice\MessageChoice;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\Translator;

class MessageChoiceTest extends ContaoTestCase
{
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

        System::setContainer($container);
    }

    public function testCollect()
    {
        $choice = new MessageChoice($this->mockContaoFramework());
        $choices = $choice->getChoices();
        $this->assertSame([], $choices);
    }
}

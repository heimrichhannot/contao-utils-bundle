<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Choice;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Choice\ModelInstanceChoice;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\Filesystem\Filesystem;

class ModelInstanceChoiceTest extends ContaoTestCase
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

//        $container = $this->mockContainer();
//
//        $kernel = $this->mockAdapter(['getCacheDir', 'isDebug']);
//        $kernel->method('getCacheDir')->willReturn($this->getTempDir());
//        $kernel->method('isDebug')->willReturn(false);
//        $container->set('kernel', $kernel);
//
//        $instance1 = $this->mockClassWithProperties(ModelUtil::class, ['id' => 12]);
//        $instance1->method('next')->willReturnSelf();
//
//        System::setContainer($container);
    }

    public function testCanBeInstantiated()
    {
        $choice = new ModelInstanceChoice($this->mockContaoFramework());

        $this->assertInstanceOf(ModelInstanceChoice::class, $choice);
    }
}

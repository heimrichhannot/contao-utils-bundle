<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Choice;

use Contao\Model;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Choice\ModelInstanceChoice;
use HeimrichHannot\UtilsBundle\Model\CfgTagModel;
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

        $container = $this->mockContainer();

        $kernel = $this->mockAdapter(['getCacheDir', 'isDebug']);
        $kernel->method('getCacheDir')->willReturn($this->getTempDir());
        $kernel->method('isDebug')->willReturn(false);
        $container->setParameter('kernel.debug', true);
        $container->set('kernel', $kernel);

        $modelInstances = $this->mockClassWithProperties(CfgTagModel::class, ['id' => 12]);
        $collection = $this->mockClassWithProperties(Model\Collection::class, ['id' => 12]);
        $collection->method('next')->willReturn($modelInstances, $modelInstances);
        $modelUtilAdapter = $this->mockAdapter(['findModelInstancesBy']);
        $modelUtilAdapter->method('findModelInstancesBy')->willReturn($collection);
        $container->set('huh.utils.model', $modelUtilAdapter);

        $dcaUtilMock = $this->mockAdapter(['getConfigByArrayOrCallbackOrFunction']);
        $dcaUtilMock->method('getConfigByArrayOrCallbackOrFunction')->willReturn(null);
        $container->set('huh.utils.dca', $dcaUtilMock);

        System::setContainer($container);
    }

    public function testCanBeInstantiated()
    {
        $choice = new ModelInstanceChoice($this->mockContaoFramework());

        $this->assertInstanceOf(ModelInstanceChoice::class, $choice);
    }

    public function testCollect()
    {
        $choice = new ModelInstanceChoice($this->mockContaoFramework());
        $choices = $choice->getChoices(['dataContainer' => 'tl_member', 'columns' => [], 'values' => [], 'options' => [], 'labelPattern' => false, 'skipSorting' => false]);
        $this->assertSame(['12' => '  (ID 12)'], $choices);
    }

    public function testCollectDefault()
    {
        $GLOBALS['TL_DCA']['tl_test']['fields']['name'] = 'name';

        $choice = new ModelInstanceChoice($this->mockContaoFramework());
        $choices = $choice->getChoices(['dataContainer' => 'tl_test', 'columns' => [], 'values' => [], 'options' => [], 'labelPattern' => false, 'skipSorting' => false]);
        $this->assertSame(['12' => ''], $choices);

        $container = System::getContainer();
        $modelUtilAdapter = $this->mockAdapter(['findModelInstancesBy']);
        $modelUtilAdapter->method('findModelInstancesBy')->willReturn(null);
        $container->set('huh.utils.model', $modelUtilAdapter);
        System::setContainer($container);

        $choices = $choice->getChoices();
        $this->assertSame([], $choices);
    }
}

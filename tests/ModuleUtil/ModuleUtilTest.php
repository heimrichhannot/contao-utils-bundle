<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Module;

use Contao\Module;
use Contao\ModuleModel;
use Contao\ModuleNavigation;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Module\ModuleUtil;
use HeimrichHannot\UtilsBundle\Tests\ModelMockTrait;

class ModuleUtilTest extends ContaoTestCase
{
    use ModelMockTrait;

    public function createModuleUtil()
    {
        $moduleMock = $this->mockAdapter(['findClass']);
        $moduleMock->method('findClass')->willReturnCallback(function ($type) {
            return '';
        });

        $framework = $this->mockContaoFramework([
            Module::class => $moduleMock,
        ]);

        return new ModuleUtil($framework);
    }

    public function testIsSubModuleOf()
    {
        $class1 = new class() extends ModuleNavigation {
            public function __construct()
            {
            }
        };

        $GLOBALS['FE_MOD']['test']['navigation_test'] = \get_class($class1);
        $GLOBALS['FE_MOD']['navigation']['navigation'] = ModuleNavigation::class;

        $moduleUtil = $this->createModuleUtil();
        $this->assertFalse($moduleUtil->isSubModuleOf('a', 'b'));

        $this->assertTrue($moduleUtil->isSubModuleOf($class1, ModuleNavigation::class));
        $this->assertTrue($moduleUtil->isSubModuleOf(\get_class($class1), ModuleNavigation::class));
        $this->assertTrue($moduleUtil->isSubModuleOf($class1, 'navigation'));
        $this->assertTrue($moduleUtil->isSubModuleOf(\get_class($class1), 'navigation'));

        $this->assertFalse($moduleUtil->isSubModuleOf($class1, 'navigation_a'));
        $this->assertFalse($moduleUtil->isSubModuleOf(\get_class($class1), 'navigation_a'));

        $this->assertTrue($moduleUtil->isSubModuleOf('navigation_test', ModuleNavigation::class));
        $this->assertTrue($moduleUtil->isSubModuleOf('navigation_test', 'navigation'));

        $moduleModelMock = $this->mockModelObject(ModuleModel::class, ['type' => 'navigation_test']);
        $this->assertTrue($moduleUtil->isSubModuleOf($moduleModelMock, ModuleNavigation::class));
        $this->assertTrue($moduleUtil->isSubModuleOf($moduleModelMock, 'navigation'));

        $moduleModelMock = $this->mockModelObject(ModuleModel::class, ['type' => 'navigation']);
        $this->assertTrue($moduleUtil->isSubModuleOf('navigation_test', $moduleModelMock));
        $this->assertTrue($moduleUtil->isSubModuleOf($class1, $moduleModelMock));
    }
}

<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Model;

use Contao\ContentModel;
use Contao\Database;
use Contao\Model;
use Contao\ModuleModel;
use Contao\System;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Model\CfgTagModel;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Tests\TestCaseEnvironment;

class ModelUtilTest extends TestCaseEnvironment
{
    public function setUpContaoFrameworkMock()
    {
        $framework = $this->mockContaoFramework();
        $framework->method('createInstance')->willReturnCallback(function ($type) {
            switch ($type) {
                case Database::class:
                    return $this->getMockBuilder(Database::class)->disableOriginalConstructor()->setMethods(null)->getMock();
            }
        });
    }

    public function testInstantiation()
    {
        $util = new ModelUtil($this->mockContaoFramework());
        $this->assertInstanceOf(ModelUtil::class, $util);
    }

    public function testSetDefaultsFromDca()
    {
        $container = System::getContainer();
        $container->set('huh.utils.dca', new DcaUtil($this->mockContaoFramework()));

        $dbalAdapter = $this->mockAdapter(['getParams']);
        $container->set('doctrine.dbal.default_connection', $dbalAdapter);

        System::setContainer($container);

        error_reporting(E_ALL & ~E_NOTICE); //Report all errors except E_NOTICE

        $util = new ModelUtil($this->mockContaoFramework());

        $GLOBALS['loadDataContainer']['tl_module'] = true;
        $GLOBALS['TL_DCA']['tl_module']['fields']['test'] = ['default' => 'test'];

        $model = $util->setDefaultsFromDca(new ModuleModel());

        $this->assertSame('test', $model->test);
    }

    public function testFindModelInstanceByPk()
    {
        $util = new ModelUtil($this->prepareFramework());
        $this->assertNull($util->findModelInstanceByPk('tl_null', 5));
        $this->assertNull($util->findModelInstanceByPk('tl_null_class', 5));
        $this->assertNull($util->findModelInstanceByPk('tl_content', 4));
        $this->assertNull($util->findModelInstanceByPk('tl_content', 'content_null'));
        $this->assertSame(5, $util->findModelInstanceByPk('tl_content', 5)->id);
        $this->assertSame('alias', $util->findModelInstanceByPk('tl_content', 'alias')->alias);
    }

    public function testFindModelInstancesBy()
    {
        $util = new ModelUtil($this->prepareFramework());
        $this->assertNull($util->findModelInstancesBy('tl_null', ['id'], [5]));
        $this->assertNull($util->findModelInstancesBy('tl_null_class', ['id'], [5]));
        $this->assertSame(5, $util->findModelInstancesBy('tl_content', ['id'], [5])->id);
        $this->assertSame(5, $util->findModelInstancesBy('tl_content', ['pid'], [3])->current()->id);
    }

    public function testFindOneModelInstanceBy()
    {
        $util = new ModelUtil($this->prepareFramework());
        $result = $util->findOneModelInstanceBy('tl_content', [], []);
        $this->assertInstanceOf(ContentModel::class, $result);

        $result = $util->findOneModelInstanceBy('null', [], []);
        $this->assertNull($result);

        $result = $util->findOneModelInstanceBy('tl_cfg_tag', [], []);
        $this->assertNull($result);
    }

    public function testFindRootParentRecursively()
    {
        $modelAdapter = $this->mockAdapter([
            'getClassFromTable',
        ]);
        $modelAdapter->method('getClassFromTable')->with($this->anything())->willReturnCallback(function ($table) {
            switch ($table) {
                case 'tl_content':
                    return ContentModel::class;
                case 'tl_null_class':
                    return 'Huh\Null\Class\Nullclass';
                case 'tl_cfg_tag':
                    return CfgTagModel::class;
                case 'null':
                    return null;
                default:
                    return null;
            }
        });
        $contentModelAdapter = $this->mockAdapter([
            'findByPk',
        ]);
        $contentModelAdapter->method('findByPk')->willReturn($this->getModel(true));
        $util = new ModelUtil($this->mockContaoFramework([Model::class => $modelAdapter, ContentModel::class => $contentModelAdapter]));
        $result = $util->findRootParentRecursively('id', 'tl_content', $this->getModel());
        $this->assertInstanceOf(\Contao\Model::class, $result);
    }

    public function testFindParentsRecursively()
    {
        $modelAdapter = $this->mockAdapter([
            'getClassFromTable',
        ]);
        $modelAdapter->method('getClassFromTable')->with($this->anything())->willReturnCallback(function ($table) {
            switch ($table) {
                case 'tl_content':
                    return ContentModel::class;
                case 'tl_null_class':
                    return 'Huh\Null\Class\Nullclass';
                case 'tl_cfg_tag':
                    return CfgTagModel::class;
                case 'null':
                    return null;
                default:
                    return null;
            }
        });
        $contentModelAdapter = $this->mockAdapter([
            'findByPk',
        ]);
        $contentModelAdapter->method('findByPk')->willReturn($this->getModel(true));
        $util = new ModelUtil($this->mockContaoFramework([Model::class => $modelAdapter, ContentModel::class => $contentModelAdapter]));
        $result = $util->findParentsRecursively('id', 'tl_content', $this->getModel());
        $this->assertInstanceOf(\PHPUnit_Framework_MockObject_MockObject::class, $result[0]);

        $result = $util->findParentsRecursively('id', 'tl_content', $this->getModel(true));
        $this->assertSame([], $result);
    }

    public function prepareFramework()
    {
        $modelAdapter = $this->mockAdapter([
            'getClassFromTable',
        ]);
        $modelAdapter->method('getClassFromTable')->with($this->anything())->willReturnCallback(function ($table) {
            switch ($table) {
                case 'tl_content':
                    return ContentModel::class;
                case 'tl_null_class':
                    return 'Huh\Null\Class\Nullclass';
                case 'tl_cfg_tag':
                    return CfgTagModel::class;
                case 'null':
                    return null;
                default:
                    return null;
            }
        });

        $contentModel = $this->mockClassWithProperties(ContentModel::class, [
            'id' => 5,
            'alias' => 'alias',
            'pid' => 3,
        ]);

        $contentModelAdapter = $this->createContentModelAdapter($contentModel);

        $framework = $this->mockContaoFramework([
            Model::class => $modelAdapter,
            ContentModel::class => $contentModelAdapter,
            CfgTagModel::class => null,
        ]);

        return $framework;
    }

    public function createContentModelAdapter($contentModel)
    {
        $contentModelAdapter = $this->mockAdapter([
            'findByPk',
            'findBy',
            'findOneBy',
        ]);
        $contentModelAdapter->method('findByPk')->with($this->anything(), $this->anything())->willReturnCallback(function ($pk, $option) use ($contentModel) {
            switch ($pk) {
                case 'alias':
                    return $contentModel;
                case 5:
                    return $contentModel;
                default:
                    return null;
            }
        });
        $contentModelAdapter->method('findBy')->with($this->anything(), $this->anything(), $this->anything())->willReturnCallback(function ($columns, $values, $options = []) use ($contentModel) {
            if ('id' === $columns[0] && 5 === $values[0]) {
                return $contentModel;
            }
            if ('pid' === $columns[0] && 3 === $values[0]) {
                $collection = new Model\Collection([$contentModel], 'tl_content');

                return $collection;
            }

            return null;
        });

        $model = $this->createMock(ContentModel::class);
        $contentModelAdapter->method('findOneBy')->willReturn($model);

        return $contentModelAdapter;
    }

    /**
     * @return \Contao\Model | \PHPUnit_Framework_MockObject_MockObject
     */
    public function getModel($idNull = false)
    {
        if ($idNull) {
            return $this->mockClassWithProperties(Model::class, ['id' => null]);
        }

        return $this->mockClassWithProperties(Model::class, ['id' => 5]);
    }
}

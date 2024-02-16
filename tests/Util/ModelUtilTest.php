<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Util;

use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\Model;
use Contao\PageModel;
use Contao\System;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Schema;
use HeimrichHannot\UtilsBundle\Tests\AbstractUtilsTestCase;
use HeimrichHannot\UtilsBundle\Tests\Util\Model\CfgTagModel;
use HeimrichHannot\UtilsBundle\Util\ModelUtil;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;

class ModelUtilTest extends AbstractUtilsTestCase
{
    private Adapter|Model|MockObject $modelAdapter;

    protected function setUp(): void
    {
        $this->modelAdapter = $this->mockModelAdapter();
    }


    public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null)
    {
        $contaoFramework = $parameters['framework'] ?? $this->mockContaoFramework([
            Model::class => $this->modelAdapter,
        ]);

        $insertTagParser = $parameters['insertTagParser'] ?? $this->createMock(InsertTagParser::class);

        return new ModelUtil($contaoFramework, $insertTagParser);
    }

    /**
     * @runInSeparateProcess
     */
    public function testAddPublishedCheckToModelArrays()
    {
        $instance = $this->getTestInstance();

        $columns = [];
        $instance->addPublishedCheckToModelArrays('tl_test', $columns);
        $this->assertCount(1, $columns);
        $this->assertStringStartsWith("(tl_test.start=''", $columns[0]);
        $this->assertStringEndsWith("tl_test.published='1'", $columns[0]);

        $columns = [];
        $instance->addPublishedCheckToModelArrays('tl_test', $columns, [
            'publishedField' => 'visible',
            'startField' => 'show',
            'stopField' => 'hide',
        ]);
        $this->assertStringStartsWith("(tl_test.show=''", $columns[0]);
        $this->assertStringEndsWith("tl_test.visible='1'", $columns[0]);
        $this->assertTrue(false !== strpos($columns[0], "tl_test.hide=''"));
        $this->assertTrue(false !== strpos($columns[0], "tl_test.show<='"));
        $this->assertTrue(false !== strpos($columns[0], "tl_test.hide>'"));

        $columns = [
            'tl_text.field=?',
        ];
        $instance->addPublishedCheckToModelArrays('tl_test', $columns, [
            'invertPublishedField' => true,
            'publishedField' => 'hidden',
        ]);
        $this->assertCount(2, $columns);
        $this->assertStringEndsWith("tl_test.hidden!='1'", $columns[1]);

        $columns = [];
        $instance->addPublishedCheckToModelArrays('tl_test', $columns, ['invertStartStopFields' => true]);
        $this->assertStringStartsWith("(tl_test.start!=''", $columns[0]);
        $this->assertTrue(false !== strpos($columns[0], "tl_test.stop!=''"));
        $this->assertTrue(false !== strpos($columns[0], "tl_test.start>'"));
        $this->assertTrue(false !== strpos($columns[0], "tl_test.stop<='"));

        \define('BE_USER_LOGGED_IN', true);
        $columns = [];
        $instance->addPublishedCheckToModelArrays('tl_test', $columns);
        $this->assertEmpty($columns);
    }

    public function testFindModelInstancesBy()
    {
        $framework = $this->mockContaoFramework([
            Model::class => $this->mockModelAdapter(),
            ContentModel::class => $this->mockContentModelAdapter(),
            Controller::class => $this->mockControllerAdapter(),
        ]);

        $util = $this->getTestInstance(['framework' => $framework]);

        $this->assertNull($util->findModelInstancesBy('tl_null', ['id=?'], [5]));
        $this->assertSame(5, $util->findModelInstancesBy('tl_content', ['id=?'], [5])->id);
        $this->assertSame(5, $util->findModelInstancesBy('tl_content', ['pid=?'], [3])->current()->id);
        $this->assertCount(2, $util->findModelInstancesBy('tl_content', null, null));
    }

    public function testFindModelInstanceByPk()
    {
        $framework = $this->mockContaoFramework([
            Model::class => $this->mockModelAdapter(),
            ContentModel::class => $this->mockContentModelAdapter(),
        ]);

        $instance = $this->getTestInstance(['framework' => $framework]);
        $this->assertNull($instance->findModelInstanceByPk('tl_null', 5));
//        $this->assertNull($instance->findModelInstanceByPk('tl_non_existing', 5));
        $this->assertNull($instance->findModelInstanceByPk('tl_content', 4));
        $this->assertNull($instance->findModelInstanceByPk('tl_content', 'content_null'));
        $this->assertSame(5, $instance->findModelInstanceByPk('tl_content', 5)->id);
    }

    public function testFindOneModelInstanceBy()
    {
        $framework = $this->mockContaoFramework([
            Model::class => $this->mockModelAdapter(),
            ContentModel::class => $this->mockContentModelAdapter(),
            Controller::class => $this->mockControllerAdapter(),
        ]);

        $util = $this->getTestInstance(['framework' => $framework]);

        $result = $util->findOneModelInstanceBy('tl_content', [], []);
        $this->assertInstanceOf(ContentModel::class, $result);

        $result = $util->findOneModelInstanceBy('null', [], []);
        $this->assertNull($result);
    }

    public function testFindMultipleModelInstancesByIds()
    {
        $framework = $this->mockContaoFramework([
            Model::class => $this->mockModelAdapter(),
            ContentModel::class => $this->mockContentModelAdapter(),
        ]);
        $instance = $this->getTestInstance(['framework' => $framework]);

        $this->assertNull($instance->findMultipleModelInstancesByIds('tl_null', [1, 3, 4]));
        $this->assertNull($instance->findMultipleModelInstancesByIds('tl_non_existing', [1, 3, 4]));

        $this->assertCount(1, $instance->findMultipleModelInstancesByIds('tl_content', [5]));
        $this->assertCount(2, $instance->findMultipleModelInstancesByIds('tl_content', [5, 7]));
        $this->assertCount(2, $instance->findMultipleModelInstancesByIds('tl_content', [0, 5, 7]));
    }

    public function testFindModelInstanceByIdOrAlias()
    {
        $framework = $this->mockContaoFramework([
            Model::class => $this->mockModelAdapter(),
            ContentModel::class => $this->mockContentModelAdapter(),
        ]);
        $instance = $this->getTestInstance(['framework' => $framework]);

        $this->assertNull($instance->findModelInstanceByIdOrAlias('tl_null', 1));
        $this->assertNull($instance->findModelInstanceByIdOrAlias('tl_non_existing', 'some-alias'));

        $this->assertSame(5, $instance->findModelInstanceByIdOrAlias('tl_content', 5)->id);
        $this->assertSame(7, $instance->findModelInstanceByIdOrAlias('tl_content', 'seven')->id);
    }

    public function testFindParentsRecursively()
    {
        System::setContainer($this->getContainerWithContaoConfiguration());
        System::getContainer()->setParameter('contao.resources_paths', $this->getFixturesPath().'/contao');
        $connection = $this->createMock(Connection::class);
        $connection->method('createSchemaManager')->willReturnCallback(function () {
            $schemaManager =  $this->createMock(AbstractSchemaManager::class);
            $schema = $this->createMock(Schema::class);
            $schema->method('getTables')->willReturn([]);
            $schemaManager->method('createSchema')->willReturn($schema);
            $schemaManager->method('introspectSchema')->willReturn($schema);
            return $schemaManager;
        });
        System::getContainer()->set('database_connection', $connection);
        $pageModel = new PageModel();

        $pageModel1 = (new PageModel())->setRow(['id' => 1, 'pid' => 0]);
        $pageModel2 = (new PageModel())->setRow(['id' => 2, 'pid' => 1]);
        $pageModel3 = (new PageModel())->setRow(['id' => 3, 'pid' => 2]);

        $pageModelAdapter = $this->mockAdapter(['findByPk']);
        $pageModelAdapter->method('findByPk')->willReturnCallback(function ($id) use ($pageModel1, $pageModel2) {
            return match ($id) {
                1 => $pageModel1,
                2 => $pageModel2,
                default => null
            };
        });

        $framework = $this->mockContaoFramework([
            PageModel::class => $pageModelAdapter,
            Model::class => $this->modelAdapter,
        ]);

        $instance = $this->getTestInstance(['framework' => $framework]);
        static::assertEmpty($instance->findParentsRecursively($pageModel));
        static::assertEmpty($instance->findParentsRecursively($pageModel1));
        static::assertCount(1, $instance->findParentsRecursively($pageModel2));
        static::assertCount(2, $instance->findParentsRecursively($pageModel3));



        return;



        $modelAdapter = $this->mModelockAdapter(
            [
                'getClassFromTable',
            ]
        );
        $modelAdapter->method('getClassFromTable')->with($this->anything())->willReturnCallback(
            function ($table) {
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
            }
        );
        $contentModelAdapter = $this->mockAdapter(
            [
                'findByPk',
            ]
        );
        $contentModelAdapter->method('findByPk')->willReturn($this->getModel(true));
        $contaoFramework = $this->mockContaoFramework([Model::class => $modelAdapter, ContentModel::class => $contentModelAdapter]);

        $util = $this->getTestInstance(['framework' => $contaoFramework]);

        $result = $util->findParentsRecursively('id', 'tl_content', $this->getModel());
        $this->assertInstanceOf(MockObject::class, $result[0]);

        $result = $util->findParentsRecursively('id', 'tl_content', $this->getModel(true));
        $this->assertSame([], $result);
    }
}

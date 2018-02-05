<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Model;

use Contao\ContentModel;
use Contao\Model;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class ModelUtilTest extends ContaoTestCase
{
    public function testInstantiation()
    {
        $util = new ModelUtil($this->mockContaoFramework());
        $this->assertInstanceOf(ModelUtil::class, $util);
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

    public function prepareFramework()
    {
        $modelAdapter = $this->mockAdapter([
            'getClassFromTable',
        ]);
        $modelAdapter
            ->method('getClassFromTable')
            ->with($this->anything())->willReturnCallback(function ($table) {
                switch ($table) {
                    case 'tl_content':
                        return ContentModel::class;
                    case 'tl_null_class':
                        return 'Huh\Null\Class\Nullclass';
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
        ]);

        return $framework;
    }

    public function createContentModelAdapter($contentModel)
    {
        $contentModelAdapter = $this->mockAdapter([
            'findByPk',
            'findBy',
        ]);
        $contentModelAdapter
            ->method('findByPk')
            ->with($this->anything(), $this->anything())
            ->willReturnCallback(function ($pk, $option) use ($contentModel) {
                switch ($pk) {
                    case 'alias':
                        return $contentModel;
                    case 5:
                        return $contentModel;
                    default:
                        return null;
                }
            });
        $contentModelAdapter
            ->method('findBy')
            ->with($this->anything(), $this->anything(), $this->anything())
            ->willReturnCallback(function ($columns, $values, $options = []) use ($contentModel) {
                if ('id' === $columns[0] && 5 === $values[0]) {
                    return $contentModel;
                }
                if ('pid' === $columns[0] && 3 === $values[0]) {
                    $collection = new Model\Collection([$contentModel], 'tl_content');

                    return $collection;
                }

                return null;
            });

        return $contentModelAdapter;
    }
}

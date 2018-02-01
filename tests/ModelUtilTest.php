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
            })
        ;

        $newsModel = $this->mockClassWithProperties(ContentModel::class, [
            'id' => 5,
            'alias' => 'alias',
        ]);

        $contentModelAdapter = $this->mockAdapter(['findByPk']);
        $contentModelAdapter
            ->method('findByPk')
                ->with($this->anything(), $this->anything())
                ->willReturnCallback(function ($pk, $option) use ($newsModel) {
                    switch ($pk) {
                        case 'alias':
                            return $newsModel;
                        case 5:
                            return $newsModel;
                        default:
                            return null;
                    }
                })
        ;

        $framework = $this->mockContaoFramework([
            Model::class => $modelAdapter,
            ContentModel::class => $contentModelAdapter,
        ]);

        return $framework;
    }
}

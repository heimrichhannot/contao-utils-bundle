<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests;

use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Framework\Adapter;
use Contao\Model;
use Contao\Model\Collection;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Model\CfgTagModel;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;

abstract class AbstractUtilsTestCase extends ContaoTestCase
{
    use ModelMockTrait;

    abstract public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null);

    /**
     * @return Adapter|MockObject|Model
     */
    protected function adapterModelClass()
    {
        $adapter = $this->mockAdapter(['getClassFromTable']);
        $adapter->method('getClassFromTable')->willReturnCallback(function ($strTable) {
            switch ($strTable) {
               case 'tl_content':
                   return ContentModel::class;

               case 'tl_cfg_tag':
                   return CfgTagModel::class;

               case 'tl_non_existing':
                   return 'HeimrichHannot\UtilsBundle\Model\NonExistingModel';

               case 'null':
               default:
                   return null;
           }
        });

        return $adapter;
    }

    /**
     * @return Adapter|MockObject|ContentModel
     */
    protected function adapterContentModelClass()
    {
        $contentModelId5 = $this->mockModelObject(ContentModel::class, [
            'id' => 5,
            'pid' => 3,
            'alias' => 'number-5',
        ]);
        $contentModelId7 = $this->mockModelObject(ContentModel::class, [
            'id' => 7,
            'pid' => 3,
            'alias' => 'seven',
        ]);

        $contentAdapter = $this->mockAdapter(['findBy', 'findByPk', 'findOneBy', 'findByIdOrAlias']);

        $contentAdapter->method('findBy')->willReturnCallback(
            function ($columns, $values, $options) use ($contentModelId5, $contentModelId7) {
                $ids = [];

                if (null === $columns) {
                    return new Collection([$contentModelId5, $contentModelId7], 'tl_content');
                }

                if ('id=?' === $columns[0] && 5 === (int) $values[0]) {
                    return $contentModelId5;
                }

                if ('pid=?' === $columns[0] && 3 === (int) $values[0]) {
                    return new Collection([$contentModelId5, $contentModelId7], 'tl_content');
                }

                if ('tl_content.id IN(' === substr($columns[0], 0, \strlen('tl_content.id IN('))) {
                    $ids = substr($columns[0], \strlen('tl_content.id IN('), -1);
                    $ids = explode(',', $ids);
                }

                $collection = [];

                if (!empty($ids)) {
                    if (\in_array(5, $ids)) {
                        $collection[] = $contentModelId5;
                    }

                    if (\in_array(7, $ids)) {
                        $collection[] = $contentModelId7;
                    }
                }

                if (!empty($collection)) {
                    return new Collection($collection, 'tl_content');
                }

                return null;
            }
        );

        $contentAdapter->method('findByPk')->willReturnCallback(
            function ($varValue, array $arrOptions = []) use ($contentModelId5, $contentModelId7) {
                switch ($varValue) {
                    case 5:
                        return $contentModelId5;

                    case 7:
                        return $contentModelId7;
                }

                return null;
            }
        );

        $contentAdapter->method('findOneBy')->willReturnCallback(
            function ($varValue, array $arrOptions = []) use ($contentModelId5, $contentModelId7) {
                switch ($varValue) {
                    case 5:
                        return $contentModelId5;

                    case 7:
                        return $contentModelId7;
                }

                return $contentModelId5;
            }
        );

        $contentAdapter->method('findByIdOrAlias')->willReturnCallback(
            function ($varValue, array $arrOptions = []) use ($contentModelId5, $contentModelId7) {
                switch ($varValue) {
                    case 5:
                    case $contentModelId5->alias:
                        return $contentModelId5;

                    case 7:
                    case $contentModelId7->alias:
                        return $contentModelId7;
                }

                return $contentModelId5;
            }
        );

        return $contentAdapter;
    }

    /**
     * @return Adapter|MockObject|Controller
     */
    protected function adapterControllerClass()
    {
        $adapter = $this->mockAdapter(['replaceInsertTags']);
        $adapter->method('replaceInsertTags')->willReturnCallback(function ($strBuffer, $blnCache = true) {
            return $strBuffer;
        });

        return $adapter;
    }
}

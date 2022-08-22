<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\EntityFinder;

use Contao\Model\Collection;
use Contao\ModuleModel;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;

class EntityFinderHelper
{
    /**
     * @var DatabaseUtil
     */
    private $databaseUtil;

    public function __construct(DatabaseUtil $databaseUtil)
    {
        $this->databaseUtil = $databaseUtil;
    }

    /**
     * @param string $type   Module type
     * @param string $field  Field with serialized data
     * @param array  $values Values to search for in serialized data field
     *
     * @throws \Exception
     */
    public function findModelByTypeAndSerializedValue(string $type, string $field, array $values): ?Collection
    {
        [$columns[], $values] = $this->databaseUtil->createWhereForSerializedBlob(ModuleModel::getTable().'.'.$field, $values);
        $columns[] = ModuleModel::getTable().'.type=?';
        $values[] = $type;

        return ModuleModel::findBy($columns, $values);
    }
}

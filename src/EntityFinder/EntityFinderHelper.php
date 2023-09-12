<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\EntityFinder;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Model\Collection;
use Contao\ModuleModel;
use HeimrichHannot\UtilsBundle\Util\Utils;

class EntityFinderHelper
{
    public function __construct(
        private Utils $utils,
        private ContaoFramework $framework,
    )
    {
    }

    /**
     * Search within serialized array fields of the model entity.
     *
     * @param string $type   Module type
     * @param string $field  Field with serialized data
     * @param array  $values Values to search for in serialized data field
     *
     * @throws \Exception
     */
    public function findModulesByTypeAndSerializedValue(string $type, string $field, array $values): ?Collection
    {
        ['columns' => $columns, 'values' => $values] = $this->utils->database()
            ->createWhereForSerializedBlob(ModuleModel::getTable().'.'.$field, $values);

        $columns = [$columns];
        $columns[] = ModuleModel::getTable().'.type=?';
        $values[] = $type;

        return $this->framework->getAdapter(ModuleModel::class)->findBy($columns, $values);
    }
}

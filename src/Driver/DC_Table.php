<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\UtilsBundle\Driver;

use Contao\DataContainer;
use Contao\Model;
use Contao\System;

class DC_Table extends DataContainer
{
    public function getPalette()
    {
    }

    /**
     * Create a DataContainer instance from a given Model.
     *
     * @param Model $model
     *
     * @return static
     */
    public static function createFromModel(Model $model)
    {
        $dc               = new static();
        $dc->strTable     = $model->getTable();
        $dc->activeRecord = $model;
        $dc->intId        = $model->id;

        return $dc;
    }

    /**
     * Create a DataContainer instance from given model data.
     *
     * @param Model  $model
     * @param string $table
     * @param string $field
     *
     * @return static
     */
    public static function createFromModelData(array $modelData, string $table, string $field = null)
    {
        $dc               = new static();
        $dc->strTable     = $table;
        $dc->activeRecord = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($table, $modelData['id']);
        $dc->intId        = $modelData['id'];

        if ($field) {
            $dc->strField = $field;
        }

        return $dc;
    }

    protected function save($varValue)
    {
    }
}

<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\UtilsBundle\Model;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Model;

class ModelUtil
{
    /**
     * @param ContaoFramework $framework
     * @param string          $table
     * @param mixed           $pk
     * @param array           $options
     *
     * @return mixed
     */
    public static function getModelInstanceByPk(ContaoFramework $framework, string $table, $pk, array $options = [])
    {
        if (!($modelClass = Model::getClassFromTable($table))) {
            return null;
        }

        if (null === ($adapter = $framework->getAdapter($modelClass))) {
            return null;
        }

        return $adapter->findByPk($pk, $options);
    }

    /**
     * @param ContaoFramework $framework
     * @param string          $table
     * @param array           $columns
     * @param array           $values
     * @param array           $options
     *
     * @return mixed
     */
    public static function getModelInstanceBy(ContaoFramework $framework, string $table, array $columns, array $values, array $options = [])
    {
        if (!($modelClass = Model::getClassFromTable($table))) {
            return null;
        }

        if (null === ($adapter = $framework->getAdapter($modelClass))) {
            return null;
        }

        return $adapter->findBy($columns, $values, $options);
    }
}

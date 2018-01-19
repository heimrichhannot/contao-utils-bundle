<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Model;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Model;

class ModelUtil
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * @param string $table
     * @param mixed  $pk
     * @param array  $options
     *
     * @return mixed
     */
    public function findModelInstanceByPk(string $table, $pk, array $options = [])
    {
        if (!($modelClass = Model::getClassFromTable($table))) {
            return null;
        }

        if (null === ($adapter = $this->framework->getAdapter($modelClass))) {
            return null;
        }

        return $adapter->findByPk($pk, $options);
    }

    /**
     * @param string $table
     * @param array  $columns
     * @param array  $values
     * @param array  $options
     *
     * @return mixed
     */
    public function findModelInstancesBy(string $table, array $columns, array $values, array $options = [])
    {
        if (!($modelClass = Model::getClassFromTable($table))) {
            return null;
        }

        if (null === ($adapter = $this->framework->getAdapter($modelClass))) {
            return null;
        }

        return $adapter->findBy($columns, $values, $options);
    }

    /**
     * @param ContaoFrameworkInterface $framework
     * @param string                   $table
     * @param array                    $columns
     * @param array                    $values
     * @param array                    $options
     *
     * @return mixed
     */
    public function findOneModelInstanceBy(string $table, array $columns, array $values, array $options = [])
    {
        if (!($modelClass = Model::getClassFromTable($table))) {
            return null;
        }

        if (null === ($adapter = $this->framework->getAdapter($modelClass))) {
            return null;
        }

        return $adapter->findOneBy($columns, $values, $options);
    }
}

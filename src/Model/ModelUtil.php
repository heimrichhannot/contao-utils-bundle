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

    /**
     * Recursively finds the root parent.
     *
     * @param string $parentProperty
     * @param string $table
     * @param Model  $instance
     * @param bool   $returnInstanceIfNoParent
     *
     * @return Model
     */
    public function findRootParentRecursively(string $parentProperty, string $table, Model $instance, bool $returnInstanceIfNoParent = true)
    {
        if (!$instance->{$parentProperty} || null === ($parentInstance = $this->findModelInstanceByPk($table, $instance->{$parentProperty}))) {
            return $returnInstanceIfNoParent ? $instance : null;
        }

        return $this->findRootParentRecursively($parentProperty, $table, $parentInstance);
    }

    /**
     * Returns an array of a model instance's parents in ascending order, i.e. the root parent comes first.
     *
     * @param string $parentProperty
     * @param string $table
     * @param Model  $instance
     *
     * @return array
     */
    public function findParentsRecursively(string $parentProperty, string $table, Model $instance): array
    {
        $parents = [];

        if (!$instance->{$parentProperty} || null === ($parentInstance = $this->findModelInstanceByPk($table, $instance->{$parentProperty}))) {
            return $parents;
        }

        return array_merge([$parentInstance], $this->findParentsRecursively($parentProperty, $table, $parentInstance));
    }
}

<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Model;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\Model;
use Contao\System;
use HeimrichHannot\UtilsBundle\Driver\DC_Table_Utils;

class ModelUtil
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Set the entity defaults from dca config (for new model entry).
     *
     * @param \Model $objModel
     *
     * @return \Model The modified model, containing the default values from all dca fields
     */
    public function setDefaultsFromDca(Model $objModel)
    {
        return System::getContainer()->get('huh.utils.dca')->setDefaultsFromDca($objModel->getTable(), $objModel);
    }

    /**
     * Returns a model instance if for a given table and id(primary key).
     * Return null, if model type or model instance with given id not exist.
     *
     * @param string $table
     * @param mixed  $pk
     * @param array  $options
     *
     * @return mixed
     */
    public function findModelInstanceByPk(string $table, $pk, array $options = [])
    {
        /* @var Model $adapter */
        if (!($modelClass = $this->framework->getAdapter(Model::class)->getClassFromTable($table))) {
            return null;
        }

        if (null === ($adapter = $this->framework->getAdapter($modelClass))) {
            return null;
        }

        return $adapter->findByPk($pk, $options);
    }

    /**
     * Returns model instances by given table and search criteria.
     *
     * @param string $table
     * @param mixed  $columns
     * @param mixed  $values
     * @param array  $options
     *
     * @return mixed
     */
    public function findModelInstancesBy(string $table, $columns, $values, array $options = [])
    {
        /* @var Model $adapter */
        if (!($modelClass = $this->framework->getAdapter(Model::class)->getClassFromTable($table))) {
            return null;
        }

        if (null === ($adapter = $this->framework->getAdapter($modelClass))) {
            return null;
        }

        $columns = $this->fixTablePrefixForDcMultilingual($table, $columns);

        return $adapter->findBy($columns, $values, $options);
    }

    /**
     * Return a single model instance by table and search criteria.
     *
     * @param string $table
     * @param array  $columns
     * @param array  $values
     * @param array  $options
     *
     * @return mixed
     */
    public function findOneModelInstanceBy(string $table, array $columns, array $values, array $options = [])
    {
        /* @var Model $adapter */
        if (!($modelClass = $this->framework->getAdapter(Model::class)->getClassFromTable($table))) {
            return null;
        }

        if (null === ($adapter = $this->framework->getAdapter($modelClass))) {
            return null;
        }

        $columns = $this->fixTablePrefixForDcMultilingual($table, $columns);

        return $adapter->findOneBy($columns, $values, $options);
    }

    /**
     * Returns multiple model instances by given table and ids.
     *
     * @param string $table
     * @param array  $ids
     * @param array  $options
     *
     * @return mixed
     */
    public function findMultipleModelInstancesByIds(string $table, array $ids, array $options = [])
    {
        /* @var Model $adapter */
        if (!($modelClass = $this->framework->getAdapter(Model::class)->getClassFromTable($table))) {
            return null;
        }

        if (null === ($adapter = $this->framework->getAdapter($modelClass))) {
            return null;
        }

        if (System::getContainer()->get('huh.utils.container')->isBundleActive('Terminal42\DcMultilingualBundle\Terminal42DcMultilingualBundle')) {
            $table = 't1';
        }

        return $adapter->findBy(["$table.id IN(".implode(',', array_map('\intval', $ids)).')'], null, $options);
    }

    /**
     * Returns multiple model instances by given table and id or alias.
     *
     * @param string $table
     * @param mixed  $idOrAlias
     * @param array  $options
     *
     * @return mixed
     */
    public function findModelInstanceByIdOrAlias(string $table, $idOrAlias, array $options = [])
    {
        if (!($modelClass = $this->framework->getAdapter(Model::class)->getClassFromTable($table))) {
            return null;
        }

        /* @var Model $adapter */
        if (null === ($adapter = $this->framework->getAdapter($modelClass))) {
            return null;
        }

        return $adapter->findByIdOrAlias($idOrAlias, $options);
    }

    /**
     * Fixes existing table prefixed already aliased in MultilingualQueryBuilder::buildQueryBuilderForFind().
     *
     * @param string $table
     * @param $columns
     *
     * @return array|mixed
     */
    public function fixTablePrefixForDcMultilingual(string $table, $columns)
    {
        Controller::loadDataContainer($table);

        if (!isset($GLOBALS['TL_DCA'][$table]['config']['dataContainer']) ||
            $GLOBALS['TL_DCA'][$table]['config']['dataContainer'] !== 'Multilingual' ||
            !System::getContainer()->get('huh.utils.container')->isBundleActive('Terminal42\DcMultilingualBundle\Terminal42DcMultilingualBundle')) {
            return $columns;
        }

        if (is_array($columns)) {
            $fixedColumns = [];

            foreach ($columns as $column) {
                $fixedColumns[] = str_replace($table.'.', 't1.', $column);
            }

            return $fixedColumns;
        }

        return str_replace($table.'.', 't1.', $columns);
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
        if (!$instance || !$instance->{$parentProperty}
            || null === ($parentInstance = $this->findModelInstanceByPk($table, $instance->{$parentProperty}))) {
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

    /**
     * Find all model instances for a given table.
     *
     * @param string $table      The table name
     * @param array  $arrOptions Additional query options
     *
     * @return Model\Collection|null
     */
    public function findAllModelInstances(string $table, array $arrOptions = []): ?Model\Collection
    {
        if (!($modelClass = $this->framework->getAdapter(Model::class)->getClassFromTable($table))) {
            return null;
        }

        /* @var Model $adapter */
        if (null === ($adapter = $this->framework->getAdapter($modelClass))) {
            return null;
        }

        return $adapter->findAll($arrOptions);
    }

    /**
     * @param string       $pattern
     * @param Model|object $instance
     * @param string       $table
     * @param array        $specialValueConfig
     *
     * @return mixed
     */
    public function computeStringPattern(string $pattern, $instance, string $table, array $specialValueConfig = [])
    {
        Controller::loadDataContainer($table);

        $dca = &$GLOBALS['TL_DCA'][$table];
        $dc = new DC_Table_Utils($table);
        $dc->id = $instance->id;
        $dc->activeRecord = $instance;

        return preg_replace_callback(
            '@%([^%]+)%@i',
            function ($matches) use ($instance, $dca, $dc, $specialValueConfig) {
                return System::getContainer()->get('huh.utils.form')->prepareSpecialValueForOutput($matches[1], $instance->{$matches[1]}, $dc, $specialValueConfig);
            },
            $pattern
        );
    }

    /**
     * @param $instance
     * @param $table
     *
     * @return Model|mixed
     */
    public function getModelInstanceIfId($instance, $table)
    {
        if ($instance instanceof Model) {
            return $instance;
        }

        if ($instance instanceof Model\Collection) {
            return $instance->current();
        }

        return $this->findModelInstanceByPk($table, $instance);
    }

    /**
     * Determine if given value is newer than DataContainer value.
     *
     * @param mixed         $newValue
     * @param DataContainer $dc
     *
     * @return bool
     */
    public function hasValueChanged($newValue, DataContainer $dc): bool
    {
        if (null !== ($entity = $this->findModelInstanceByPk($dc->table, $dc->id))) {
            return $newValue != $entity->{$dc->field};
        }

        return true;
    }

    /**
     * Get model instance value for given field.
     *
     * @param string $field
     * @param string $table
     * @param int    $id
     *
     * @return mixed|null
     */
    public function getModelInstanceFieldValue(string $field, string $table, int $id)
    {
        if (null !== ($entity = $this->findModelInstanceByPk($table, $id))) {
            return $entity->{$property};
        }

        return null;
    }
}

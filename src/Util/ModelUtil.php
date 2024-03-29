<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util;

use Contao\Controller;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\Date;
use Contao\Model;
use Contao\Model\Collection;

class ModelUtil
{
    public function __construct(
        private ContaoFramework $framework,
        private InsertTagParser $insertTagParser
    )
    {
    }

    /**
     * Adds an published check to your model query.
     *
     * Options:
     * - publishedField: The name of the published field. Default: "published"
     * - startField: The name of the start field. Default: "start"
     * - stopField: The name of the stop field. Default: "stop"
     * - invertPublishedField: Set to true, if the published field should be evaluated inverted (for "hidden" or "invisible" fields. Default: false
     * - invertStartStopFields: Set to true, if the start and stop fields should be evaluated in an inverted manner. Default: false
     * - ignoreFePreview: Set to true, frontend preview should be ignored. Default: false
     *
     * @param string $table   The table name
     * @param array  $columns The columns array
     * @param array{
     *     publishedField?: string,
     *     startField?: string,
     *     stopField?: string,
     *     invertPublishedField?: bool,
     *     invertStartStopFields?: bool,
     *     ignoreFePreview?: bool
     * }  $options pass additional options
     */
    public function addPublishedCheckToModelArrays(string $table, array &$columns, array $options = []): void
    {
        $defaults = [
            'invertPublishedField' => false,
            'invertStartStopFields' => false,
            'publishedField' => 'published',
            'startField' => 'start',
            'stopField' => 'stop',
            'ignoreFePreview' => false,
        ];
        $options = array_merge($defaults, $options);

        $t = $table;

        if ($options['ignoreFePreview'] || !(\defined('BE_USER_LOGGED_IN') && BE_USER_LOGGED_IN === true)) {
            $time = Date::floorToMinute();

            $columns[] = "($t.".$options['startField'].($options['invertStartStopFields'] ? '!=' : '=')."'' OR $t.".$options['startField'].($options['invertStartStopFields'] ? '>' : '<=')."'$time') AND ($t.".$options['stopField'].($options['invertStartStopFields'] ? '!=' : '=')."'' OR $t.".$options['stopField'].($options['invertStartStopFields'] ? '<=' : '>')."'".($time + 60)."') AND $t.".$options['publishedField'].($options['invertPublishedField'] ? '!=' : '=')."'1'";
        }
    }

    /**
     * Returns model instances by given table and search criteria.
     *
     * Options:
     * - skipReplaceInsertTags: (bool) Skip the replacement of inserttags. Default: false
     *
     * @param array{
     *     skipReplaceInsertTags?: bool
     * } $options
     *
     * @return Model[]|Collection|null
     */
    public function findModelInstancesBy(string $table, array|string|null $columns, int|string|array|null $values, array $options = []): Collection|Model|null
    {
        $defaults = [
            'skipReplaceInsertTags' => false,
        ];
        $options = array_merge($defaults, $options);

        /* @var string|null $modelClass */
        if (!($modelClass = $this->framework->getAdapter(Model::class)->getClassFromTable($table))) {
            return null;
        }

        /* @var Model $adapter */
        if (null === ($adapter = $this->framework->getAdapter($modelClass))) {
            return null;
        }

        if (is_array($values) && !$options['skipReplaceInsertTags']) {
            $values = array_map(fn($value) => $this->insertTagParser->replace($value), $values);
        }

        if (empty($columns)) {
            $columns = null;
        }

        return $adapter->findBy($columns, $values, $options);
    }

    /**
     * Find a single model instance for given table by its primary key (id).
     *
     * @param string $table The table
     * @param int|string $pk The property value
     * @param array $options An optional options array
     *
     * @return Model|null The model or null if the result is empty
     */
    public function findModelInstanceByPk(string $table, int|string $pk, array $options = []): ?Model
    {
        if (!($modelClass = $this->framework->getAdapter(Model::class)->getClassFromTable($table))) {
            return null;
        }

        if (null === ($adapter = $this->framework->getAdapter($modelClass))) {
            return null;
        }

        /** @var Model|Adapter $adapter */
        return $adapter->findByPk($pk, $options);
    }

    /**
     * Return a single model instance by table and search criteria.
     *
     * Options:
     * - skipReplaceInsertTags: Skip the replacement of inserttags. Default: false
     *
     * @param array{
     *     skipReplaceInsertTags?: bool
     * } $options
     *
     */
    public function findOneModelInstanceBy(string $table, array $columns, array $values, array $options = []): ?Model
    {
        $options = array_merge([
            'skipReplaceInsertTags' => false,
        ], $options);

        if (!($modelClass = $this->framework->getAdapter(Model::class)->getClassFromTable($table))) {
            return null;
        }


        if (null === ($adapter = $this->framework->getAdapter($modelClass))) {
            return null;
        }

        if (is_array($values) && !$options['skipReplaceInsertTags']) {
            $values = array_map(fn($value) => $this->insertTagParser->replace($value), $values);
        }

        if (empty($columns)) {
            $columns = null;
        }

        /* @var Model|Adapter $adapter */
        return $adapter->findOneBy($columns, $values, $options);
    }

    /**
     * Returns multiple model instances by given table and ids.
     */
    public function findMultipleModelInstancesByIds(string $table, array $ids, array $options = []): Collection|Model|null
    {
        if (!($modelClass = $this->framework->getAdapter(Model::class)->getClassFromTable($table))) {
            return null;
        }

        if (null === ($adapter = $this->framework->getAdapter($modelClass))) {
            return null;
        }

        /** @var Model|Adapter $adapter */
        return $adapter->findBy(["$table.id IN(".implode(',', array_map('\intval', $ids)).')'], null, $options);
    }

    /**
     * Returns model instance by given table and id or alias.
     */
    public function findModelInstanceByIdOrAlias(string $table, int|string $idOrAlias, array $options = []): ?Model
    {
        if (!($modelClass = $this->framework->getAdapter(Model::class)->getClassFromTable($table))) {
            return null;
        }

        /* @var Model $adapter */
        if (null === ($adapter = $this->framework->getAdapter($modelClass))) {
            return null;
        }

        $options = array_merge(
            [
                'limit' => 1,
                'column' => !is_numeric($idOrAlias) ? ["$table.alias=?"] : ["$table.id=?"],
                'value' => $idOrAlias,
                'return' => 'Model',
            ],
            $options
        );

        return $adapter->findByIdOrAlias($idOrAlias, $options);
    }

    /**
     * Returns an array of a model instance's parents in ascending order, i.e. the root parent comes first.
     *
     * @template T of Model
     * @param T $instance
     * @param string $parentProperty
     * @return array<T>
     */
    public function findParentsRecursively(Model $instance, string $parentProperty = 'pid'): array
    {
        $table = call_user_func([$instance, 'getTable']);

        $parents = [];
        $model = $this->framework->getAdapter(Model::class);
        $modelClass = $model->getClassFromTable($table);

        if (!$instance->{$parentProperty}) {
            return $parents;
        }

        if (null === ($parentInstance = $this->framework->getAdapter($modelClass)->findByPk($instance->{$parentProperty}))) {
            return $parents;
        }

        return array_merge($this->findParentsRecursively($parentInstance, $parentProperty), [$parentInstance]);
    }
}

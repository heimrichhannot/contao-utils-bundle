<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util\Model;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Date;
use Contao\Model;
use Contao\Model\Collection;

class ModelUtil
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFramework $contaoFramework)
    {
        $this->framework = $contaoFramework;
    }

    /**
     * Adds an published check to your model query.
     *
     * Options:
     * - publishedField: (string) The name of the published field. Default: "published"
     * - startField: (string) The name of the start field. Default: "start"
     * - stopField: (string) The name of the stop field. Default: "stop"
     * - invertPublishedField: (bool) Set to true, if the published field should be evaluated inverted (for "hidden" or "invisible" fields. Default: false
     * - invertStartStopFields: (bool) Set to true, if the start and stop fields should be evaluated in an inverted manner. Default: false
     * - ignoreFePreview: (bool) Set to true, frontend preview should be ignored. Default: false
     *
     * @param string $table   The table name
     * @param array  $columns The columns array
     * @param array  $options pass additional options
     */
    public function addPublishedCheckToModelArrays(string $table, array &$columns, array $options = [])
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
     * @param mixed $columns
     * @param mixed $values
     *
     * @return Model[]|Collection|null
     */
    public function findModelInstancesBy(string $table, $columns, $values, array $options = [])
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

        if (\is_array($values) && true !== $options['skipReplaceInsertTags']) {
            $values = array_map([$this->framework->getAdapter(Controller::class), 'replaceInsertTags'], $values);
        }

        if (empty($columns)) {
            $columns = null;
        }

        return $adapter->findBy($columns, $values, $options);
    }

    /**
     * Find a single model instance for given table by its primary key (id).
     *
     * @param string $table   The table
     * @param mixed  $pk      The property value
     * @param array  $options An optional options array
     *
     * @return Model|null The model or null if the result is empty
     */
    public function findModelInstanceByPk(string $table, $pk, array $options = []): ?Model
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
     * Return a single model instance by table and search criteria.
     *
     * Options:
     * - skipReplaceInsertTags: (bool) Skip the replacement of inserttags. Default: false
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

        if (\is_array($values) && (!isset($options['skipReplaceInsertTags']) || !$options['skipReplaceInsertTags'])) {
            $values = array_map([$this->framework->getAdapter(Controller::class), 'replaceInsertTags'], $values);
        }

        if (empty($columns)) {
            $columns = null;
        }

        return $adapter->findOneBy($columns, $values, $options);
    }

    /**
     * Returns multiple model instances by given table and ids.
     *
     * @return Collection|Model[]|Model|null
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

        return $adapter->findBy(["$table.id IN(".implode(',', array_map('\intval', $ids)).')'], null, $options);
    }

    /**
     * Returns multiple model instances by given table and id or alias.
     *
     * @param mixed $idOrAlias
     *
     * @return Model|null
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
}

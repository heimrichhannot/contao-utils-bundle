<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util\Model;

use Contao\Controller;
use Contao\CoreBundle\ContaoFrameworkInterface;
use Contao\Date;
use Contao\Model;
use Contao\Model\Collection;

class ModelUtil
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $contaoFramework)
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

        /** @var string|null $modelClass */
        if (!($modelClass = $this->framework->getAdapter(Model::class)->getClassFromTable($table))) {
            return null;
        }

        /* @var Model $adapter */
        if (null === ($adapter = $this->framework->getAdapter($modelClass))) {
            return null;
        }

//        $this->fixTablePrefixForDcMultilingual($table, $columns, $options);

        if (\is_array($values) && true !== $options['skipReplaceInsertTags']) {
            $values = array_map([$this->framework->getAdapter(Controller::class), 'replaceInsertTags'], $values);
        }

        if (empty($columns)) {
            $columns = null;
        }

        return $adapter->findBy($columns, $values, $options);
    }
}

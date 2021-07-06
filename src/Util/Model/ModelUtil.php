<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util\Model;

use Contao\Date;

class ModelUtil
{
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
}

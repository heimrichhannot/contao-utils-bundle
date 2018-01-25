<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Dca;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\DataContainer;
use Contao\System;

class DcaUtil
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Retrieves an array from a dca config (in most cases eval) in the following priorities:.
     *
     * 1. The value associated to $array[$property]
     * 2. The value retrieved by $array[$property . '_callback'] which is a callback array like ['Class', 'method']
     * 3. The value retrieved by $array[$property . '_callback'] which is a function closure array like ['Class', 'method']
     *
     * @param array $array
     * @param       $property
     * @param array $arguments
     *
     * @return mixed|null The value retrieved in the way mentioned above or null
     */
    public function getConfigByArrayOrCallbackOrFunction(array $array, $property, array $arguments = [])
    {
        if (isset($array[$property])) {
            return $array[$property];
        }

        if (is_array($array[$property.'_callback'])) {
            $callback = $array[$property.'_callback'];

            $instance = Controller::importStatic($callback[0]);

            return call_user_func_array([$instance, $callback[1]], $arguments);
        } elseif (is_callable($array[$property.'_callback'])) {
            return call_user_func_array($array[$property.'_callback'], $arguments);
        }

        return null;
    }

    /**
     * Sets the current date as the date added -> usually used on submit.
     *
     * @param DataContainer $dc
     */
    public function setDateAdded(DataContainer $dc)
    {
        $modelUtil = System::getContainer()->get('huh.utils.model');

        if (null === $dc || null === ($model = $modelUtil->findModelInstanceByPk($dc->table, $dc->id)) || $model->dateAdded > 0) {
            return;
        }

        Database::getInstance()->prepare("UPDATE $dc->table SET dateAdded=? WHERE id=? AND dateAdded = 0")->execute(time(), $dc->id);
    }

    /**
     * Sets the current date as the date added -> usually used on copy.
     *
     * @param DataContainer $dc
     */
    public function setDateAddedOnCopy($insertId, DataContainer $dc)
    {
        $modelUtil = System::getContainer()->get('huh.utils.model');

        if (null === $dc || null === ($model = $modelUtil->findModelInstanceByPk($dc->table, $insertId)) || $model->dateAdded > 0) {
            return;
        }

        Database::getInstance()->prepare("UPDATE $dc->table SET dateAdded=? WHERE id=? AND dateAdded = 0")->execute(time(), $insertId);
    }

    /**
     * Returns a list of fields as an option array for dca fields.
     *
     * @param string $table
     * @param array  $options
     *
     * @return array
     */
    public function getFields(string $table, array $options = []): array
    {
        $fields = [];

        if (!$table) {
            return $fields;
        }

        Controller::loadDataContainer($table);
        System::loadLanguageFile($table);

        if (!isset($GLOBALS['TL_DCA'][$table]['fields'])) {
            return $fields;
        }

        foreach ($GLOBALS['TL_DCA'][$table]['fields'] as $name => $data) {
            // restrict to certain input types
            if (is_array($options['inputTypes']) && !empty($options['inputTypes']) && !in_array($data['inputType'], $options['inputTypes'], true)) {
                continue;
            }

            if (!$options['localizeLabels']) {
                $fields[$name] = $name;
            } else {
                $fields[$name] = ($data['label'][0] ?: $name).($data['label'][0] ? ' ['.$name.']' : '');
            }
        }

        if (!$options['skipSorting']) {
            asort($fields);
        }

        return $fields;
    }

    /**
     * Adds an override selector to every field in $fields to the dca associated with $destinationTable.
     *
     * @param array  $fields
     * @param string $sourceTable
     * @param string $destinationTable
     * @param array  $options
     */
    public function addOverridableFields(array $fields, string $sourceTable, string $destinationTable, array $options = [])
    {
        Controller::loadDataContainer($sourceTable);
        System::loadLanguageFile($sourceTable);
        $sourceDca = $GLOBALS['TL_DCA'][$sourceTable];

        Controller::loadDataContainer($destinationTable);
        System::loadLanguageFile($destinationTable);
        $destinationDca = &$GLOBALS['TL_DCA'][$destinationTable];

        foreach ($fields as $field) {
            // add override boolean field
            $overrideFieldname = 'override'.ucfirst($field);

            $destinationDca['fields'][$overrideFieldname] = [
                'label' => &$GLOBALS['TL_LANG'][$destinationTable][$overrideFieldname],
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => ['tl_class' => 'w50', 'submitOnChange' => true],
                'sql' => "char(1) NOT NULL default ''",
            ];

            if ($options['checkboxDcaEvalOverride']) {
                $destinationDca['fields'][$overrideFieldname]['eval'] = array_merge(
                    $destinationDca['fields'][$overrideFieldname]['eval'],
                    $options['checkboxDcaEvalOverride']
                );
            }

            // important: nested selectors need to be in reversed order -> see DC_Table::getPalette()
            $destinationDca['palettes']['__selector__'] = array_merge([$overrideFieldname], $destinationDca['palettes']['__selector__']);

            // copy field
            $destinationDca['fields'][$field] = $sourceDca['fields'][$field];

            // subpalette
            $destinationDca['subpalettes'][$overrideFieldname] = $field;

            if (!$options['skipLocalization']) {
                $GLOBALS['TL_LANG'][$destinationTable][$overrideFieldname] = [
                    System::getContainer()->get('translator')->trans(
                        'huh.utils.misc.override.label',
                        [
                            '%fieldname%' => $GLOBALS['TL_LANG'][$sourceTable][$field][0],
                        ]
                    ),
                    System::getContainer()->get('translator')->trans(
                        'huh.utils.misc.override.desc',
                        [
                            '%fieldname%' => $GLOBALS['TL_LANG'][$sourceTable][$field][0],
                        ]
                    ),
                ];
            }
        }
    }

    /**
     * Retrieves a property of given contao model instances by *ascending* priority, i.e. the last instance of $instances
     * will have the highest priority.
     *
     * CAUTION: This function assumes that you have used addOverridableFields() in this class!! That means, that a value in a
     * model instance is only used if it's either the first instance in $arrInstances or "overrideFieldname" is set to true
     * in the instance.
     *
     * @param string $property  The property name to retrieve
     * @param array  $instances An array of instances in ascending priority. Instances can be passed in the following form:
     *                          ['tl_some_table', $instanceId] or $objInstance
     *
     * @return mixed
     */
    public function getOverridableProperty(string $property, array $instances)
    {
        $result = null;
        $preparedInstances = [];

        // prepare instances
        foreach ($instances as $instance) {
            if (is_array($instance)) {
                if (null !== ($objInstance = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($instance[0], $instance[1]))) {
                    $preparedInstances[] = $objInstance;
                }
            } elseif ($instance instanceof \Model) {
                $preparedInstances[] = $instance;
            }
        }

        foreach ($preparedInstances as $i => $preparedInstance) {
            if (0 == $i || $preparedInstance->{'override'.ucfirst($property)}) {
                $result = $preparedInstance->{$property};
            }
        }

        return $result;
    }

    /**
     * This function transforms an entity's palette (that can also contain sub palettes and concatenated type selectors) to a flatten
     * palette where every field can be overridden.
     *
     * CAUTION: This function assumes that you have used addOverridableFields() for adding the fields that are overridable. The latter ones
     * are $overridableFields
     *
     * This function is useful if you want to adjust a palette for sub entities that can override properties of their ancestor(s).
     * Use $this->getOverridableProperty() for computing the correct value respecting the entity hierarchy.
     *
     * @param string $table
     */
    public function flattenPaletteForSubEntities(string $table, $overridableFields)
    {
        Controller::loadDataContainer($table);

        $dca = &$GLOBALS['TL_DCA'][$table];
        $arrayUtil = System::getContainer()->get('huh.utils.array');

        // palette
        foreach ($overridableFields as $field) {
            if ($dca['fields'][$field]['eval']['submitOnChange'] === true) {
                unset($dca['fields'][$field]['eval']['submitOnChange']);

                if (in_array($field, $dca['palettes']['__selector__'], true)) {
                    // flatten concatenated type selectors
                    foreach ($dca['subpalettes'] as $selector => $subPaletteFields) {
                        if (false !== strpos($selector, $field.'_')) {
                            if ($dca['subpalettes'][$selector]) {
                                $subPaletteFields = explode(',', $dca['subpalettes'][$selector]);

                                foreach (array_reverse($subPaletteFields) as $subPaletteField) {
                                    $dca['palettes']['default'] = str_replace($field, $field.','.$subPaletteField, $dca['palettes']['default']);
                                }
                            }

                            // remove nested field in order to avoid its normal "selector" behavior
                            $arrayUtil->removeValue($field, $dca['palettes']['__selector__']);
                            unset($dca['subpalettes'][$selector]);
                        }
                    }

                    // flatten sub palettes
                    if (isset($dca['subpalettes'][$field]) && $dca['subpalettes'][$field]) {
                        $subPaletteFields = explode(',', $dca['subpalettes'][$field]);

                        foreach (array_reverse($subPaletteFields) as $subPaletteField) {
                            $dca['palettes']['default'] = str_replace($field, $field.','.$subPaletteField, $dca['palettes']['default']);
                        }

                        // remove nested field in order to avoid its normal "selector" behavior
                        $arrayUtil->removeValue($field, $dca['palettes']['__selector__']);
                        unset($dca['subpalettes'][$field]);
                    }
                }
            }

            $dca['palettes']['default'] = str_replace($field, 'override'.ucfirst($field), $dca['palettes']['default']);
        }
    }
}

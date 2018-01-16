<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\UtilsBundle\Form;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\Date;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;

class FormUtil
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    public function prepareSpecialValueForOutput($field, $value, DataContainer $dc, $skipDcaLoading = false)
    {
        $value = StringUtil::deserialize($value);

        // Recursively apply logic to array
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->prepareSpecialValueForOutput($field, $v, $dc, $skipDcaLoading);
            }

            return $value;
        }

        $table = $dc->table;

        if (!$skipDcaLoading) {
            Controller::loadDataContainer($table);
            System::loadLanguageFile($table);
        }

        $data = $GLOBALS['TL_DCA'][$table]['fields'][$field];
        $options = System::getContainer()->get('huh.utils.dca')->getConfigByArrayOrCallbackOrFunction($data, 'options', [$dc]);
        $reference = $data['reference'];
        $rgxp = $data['eval']['rgxp'];

        // foreignKey
        if (isset($data['foreignKey'])) {
            list($foreignTable, $foreignField) = explode('.', $data['foreignKey']);

            if (null !== ($instance = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($foreignTable, $value))) {
                $value = $instance->{$foreignField};
            }
        }

        if ('explanation' == $data['inputType']) {
            $value = $data['eval']['text'];
        } elseif ('date' == $rgxp) {
            $value = Date::parse(Config::get('dateFormat'), $value);
        } elseif ('time' == $rgxp) {
            $value = Date::parse(Config::get('timeFormat'), $value);
        } elseif ('datim' == $rgxp) {
            $value = Date::parse(Config::get('datimFormat'), $value);
        } elseif ('multiColumnEditor' == $data['inputType'] && System::getContainer()->get('huh.utils.container')
                ->isBundleActive('multi_column_editor')) {
            if (is_array($value)) {
                $rows = [];

                foreach ($value as $row) {
                    $fields = [];

                    foreach ($row as $fieldName => $fieldValue) {
                        $dca = $data['eval']['multiColumnEditor']['fields'][$fieldName];

                        $fields[] = ($dca['label'][0] ?: $fieldName).': '.$this->prepareSpecialValueForOutput(
                            $fieldName,
                            $fieldValue,
                            $dc,
                            $skipDcaLoading
                            );
                    }

                    $rows[] = '['.implode(', ', $fields).']';
                }

                $value = implode(', ', $rows);
            }
        } elseif (Validator::isBinaryUuid($value)) {
            $strPath = Files::getPathFromUuid($value);
            $value = $strPath ? (\Environment::get('url').'/'.$strPath) : \StringUtil::binToUuid($value);
        } elseif (is_array($value)) {
            $value = Arrays::flattenArray($value);
            $value = array_filter($value); // remove empty elements

            // transform binary uuids to paths
            $value = array_map(
                function ($varValue) {
                    if (\Validator::isBinaryUuid($varValue)) {
                        $strPath = Files::getPathFromUuid($varValue);

                        if ($strPath) {
                            return \Environment::get('url').'/'.$strPath;
                        }

                        return \StringUtil::binToUuid($varValue);
                    }

                    return $varValue;
                },
                $value
            );

            if (!$reference) {
                $value = array_map(
                    function ($varValue) use ($options) {
                        return isset($options[$varValue]) ? $options[$varValue] : $varValue;
                    },
                    $value
                );
            }

            $value = array_map(
                function ($varValue) use ($reference) {
                    if (is_array($reference)) {
                        return isset($reference[$varValue]) ? ((is_array(
                            $reference[$varValue]
                        )) ? $reference[$varValue][0] : $reference[$varValue]) : $varValue;
                    }

                    return $varValue;
                },
                $value
            );
        }
        // Replace boolean checkbox value with "yes" and "no"
        else {
            if ($data['eval']['isBoolean'] || ('checkbox' == $data['inputType'] && !$data['eval']['multiple'])) {
                $value = ('' != $value) ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
            } elseif (is_array($options) && array_is_assoc($options)) {
                $value = isset($options[$value]) ? $options[$value] : $value;
            } elseif (is_array($reference)) {
                $value = isset($reference[$value]) ? ((is_array(
                    $reference[$value]
                )) ? $reference[$value][0] : $reference[$value]) : $value;
            }
        }

        if (is_array($value)) {
            $value = implode(', ', $value);
        }

        if ($data['eval']['encrypt']) {
            $value = \Encryption::decrypt($value);
        }

        // Convert special characters (see #1890)
        return specialchars($value);
    }
}

<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\UtilsBundle\Form;

use Contao\Controller;
use Contao\DataContainer;
use Contao\StringUtil;
use Contao\System;

class FormUtil
{
    public static function prepareSpecialValueForOutput($field, $value, DataContainer $dc, $skipDcaLoading = false)
    {
        $table = $dc->table;

        if (!$skipDcaLoading) {
            Controller::loadDataContainer($table);
            System::loadLanguageFile($table);
        }

        $data = $GLOBALS['TL_DCA'][$table]['fields'][$field];
        $value = StringUtil::deserialize($value);
        $arrOptions = $data['options'];
        $arrReference = $data['reference'];
        $strRegExp = $data['eval']['rgxp'];

        // get options
        if ((is_array($data['options_callback']) || is_callable($data['options_callback'])) && !$data['reference']) {
            $arrOptionsCallback = null;

            // TODO use getConfigByArrayOrCallbackOrFunction
            if (is_array($data['options_callback'])) {
                $strClass = $data['options_callback'][0];
                $strMethod = $data['options_callback'][1];

                $objInstance = \Controller::importStatic($strClass);

                $arrOptionsCallback = @$objInstance->{$strMethod}($dc);
            } elseif (is_callable($data['options_callback'])) {
                $arrOptionsCallback = @$data['options_callback']($dc);
            }

            $arrOptions = !is_array($value) ? [$value] : $value;

            if (null !== $value && is_array($arrOptionsCallback) && array_is_assoc($arrOptionsCallback)) {
                $value = array_intersect_key($arrOptionsCallback, array_flip($arrOptions));
            }
        }

        // foreignKey
        if (isset($data['foreignKey']) && !is_array($value)) {
            list($strForeignTable, $strForeignField) = explode('.', $data['foreignKey']);

            if (null !== ($objInstance = General::getModelInstance($strForeignTable, $value))) {
                $value = $objInstance->{$strForeignField};
            }
        }

        if ('explanation' == $data['inputType']) {
            $value = $data['eval']['text'];
        } elseif ('date' == $strRegExp) {
            $value = \Date::parse(\Config::get('dateFormat'), $value);
        } elseif ('time' == $strRegExp) {
            $value = \Date::parse(\Config::get('timeFormat'), $value);
        } elseif ('datim' == $strRegExp) {
            $value = \Date::parse(\Config::get('datimFormat'), $value);
        } elseif ('multiColumnEditor' == $data['inputType'] && in_array('multi_column_editor', \ModuleLoader::getActive(), true)) {
            if (is_array($value)) {
                $arrRows = [];

                foreach ($value as $arrRow) {
                    $arrFields = [];

                    foreach ($arrRow as $strField => $varFieldValue) {
                        $arrDca = $data['eval']['multiColumnEditor']['fields'][$strField];

                        $arrFields[] = ($arrDca['label'][0] ?: $strField).': '.static::prepareSpecialValueForPrint(
                                $varFieldValue,
                                $arrDca,
                                $table,
                                $dc,
                                $objItem
                            );
                    }

                    $arrRows[] = '['.implode(', ', $arrFields).']';
                }

                $value = implode(', ', $arrRows);
            }
        } elseif ('tag' == $data['inputType'] && in_array('tags_plus', \ModuleLoader::getActive(), true)) {
            if (null !== ($arrTags = \HeimrichHannot\TagsPlus\TagsPlus::loadTags($table, $objItem->id))) {
                $value = $arrTags;
            }
        } elseif (!is_array($value) && \Validator::isBinaryUuid($value)) {
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

            if (!$arrReference) {
                $value = array_map(
                    function ($varValue) use ($arrOptions) {
                        return isset($arrOptions[$varValue]) ? $arrOptions[$varValue] : $varValue;
                    },
                    $value
                );
            }

            $value = array_map(
                function ($varValue) use ($arrReference) {
                    if (is_array($arrReference)) {
                        return isset($arrReference[$varValue]) ? ((is_array(
                            $arrReference[$varValue]
                        )) ? $arrReference[$varValue][0] : $arrReference[$varValue]) : $varValue;
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
            } elseif (is_array($arrOptions) && array_is_assoc($arrOptions)) {
                $value = isset($arrOptions[$value]) ? $arrOptions[$value] : $value;
            } elseif (is_array($arrReference)) {
                $value = isset($arrReference[$value]) ? ((is_array(
                    $arrReference[$value]
                )) ? $arrReference[$value][0] : $arrReference[$value]) : $value;
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

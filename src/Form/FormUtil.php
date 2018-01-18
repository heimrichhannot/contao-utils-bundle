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
use Contao\Encryption;
use Contao\Environment;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use Haste\Model\Relations;
use HeimrichHannot\NewsBundle\Model\CfgTagModel;
use HeimrichHannot\Request\Request;

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
                $result = $this->prepareSpecialValueForOutput($field, $v, $dc, $skipDcaLoading);
                if (null !== $result && !empty($result)) {
                    $value[$k] = $result;
                } else {
                    unset($value[$k]);
                }
            }

            return implode(', ', $value);
        }

        $table = $dc->table;

        if (!$skipDcaLoading) {
            Controller::loadDataContainer($table);
            System::loadLanguageFile($table);
        }

        $data      = $GLOBALS['TL_DCA'][$table]['fields'][$field];
        $options   = System::getContainer()->get('huh.utils.dca')->getConfigByArrayOrCallbackOrFunction($data, 'options', [$dc]);
        $reference = $data['reference'];
        $rgxp      = $data['eval']['rgxp'];

        // foreignKey
        if (isset($data['foreignKey'])) {
            list($foreignTable, $foreignField) = explode('.', $data['foreignKey']);

            if (null !== ($instance = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($foreignTable, $value))) {
                $value = $instance->{$foreignField};
            }
        }

        if ('explanation' == $data['inputType']) {
            $value = $data['eval']['text'];
        } elseif ('cfgTags' == $data['inputType']) {
            $collection = CfgTagModel::findBy(['source=?', 'id = ?'], [$data['eval']['tagsManager'], $value]);
            $value      = null;
            if (null !== $collection) {
                $result = $collection->fetchEach('name');
                $value  = implode('', $result);
            }
        } elseif ('date' == $rgxp) {
            $value = Date::parse(Config::get('dateFormat'), $value);
        } elseif ('time' == $rgxp) {
            $value = Date::parse(Config::get('timeFormat'), $value);
        } elseif ('datim' == $rgxp) {
            $value = Date::parse(Config::get('datimFormat'), $value);
        } elseif ('multiColumnEditor' == $data['inputType']
                  && System::getContainer()->get('huh.utils.container')->isBundleActive('multi_column_editor')) {
            if (is_array($value)) {
                $rows = [];

                foreach ($value as $row) {
                    $fields = [];

                    foreach ($row as $fieldName => $fieldValue) {
                        $dca = $data['eval']['multiColumnEditor']['fields'][$fieldName];

                        $fields[] = ($dca['label'][0] ?: $fieldName) . ': ' . $this->prepareSpecialValueForOutput($fieldName, $fieldValue, $dc, $skipDcaLoading);
                    }

                    $rows[] = '[' . implode(', ', $fields) . ']';
                }

                $value = implode(', ', $rows);
            }
        } elseif (Validator::isBinaryUuid($value)) {
            $strPath = System::getContainer()->get('huh.utils.file')->getPathFromUuid($value);
            $value   = $strPath ? Environment::get('url') . '/' . $strPath : StringUtil::binToUuid($value);
        } // Replace boolean checkbox value with "yes" and "no"
        else {
            if ($data['eval']['isBoolean'] || ('checkbox' == $data['inputType'] && !$data['eval']['multiple'])) {
                $value = ('' != $value) ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
            } elseif (is_array($options) && array_is_assoc($options)) {
                $value = isset($options[$value]) ? $options[$value] : $value;
            }
        }

        if (is_array($reference)) {
            $value = isset($reference[$value]) ? ((is_array($reference[$value])) ? $reference[$value][0] : $reference[$value]) : $value;
        }

        if ($data['eval']['encrypt']) {
            $value = Encryption::decrypt($value);
        }

        // Convert special characters (see #1890)
        return specialchars($value);
    }

    public function escapeAllHtmlEntities($table, $field, $value)
    {
        Controller::loadDataContainer($table);

        $data = $GLOBALS['TL_DCA'][$table]['fields'][$field];

        $preservedTags = isset($data['eval']['allowedTags']) ? $data['eval']['allowedTags'] : \Config::get('allowedTags');

        if ($data['eval']['allowHtml'] || strlen($data['eval']['rte']) || $data['eval']['preserveTags']) {
            // always decode entities if HTML is allowed
            $value = Request::cleanHtml($value, true, true, $preservedTags);
        } elseif (is_array($data['options']) || isset($data['options_callback']) || isset($data['foreignKey'])) {
            // options should not be strict cleaned, as they might contain html tags like <strong>
            $value = Request::cleanHtml($value, true, true, $preservedTags);
        } else {
            $value = Request::clean($value, $data['eval']['decodeEntities'], true);
        }

        return $value;
    }
}

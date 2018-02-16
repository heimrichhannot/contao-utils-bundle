<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
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
use HeimrichHannot\NewsBundle\Model\CfgTagModel;
use HeimrichHannot\Request\Request;

class FormUtil
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    /** @var array */
    protected $optionsCache;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Prepares a special field's value. If an array is inserted, the function will call itself recursively.
     *
     * @param string        $field
     * @param               $value
     * @param DataContainer $dc
     * @param array         $config
     *
     * @return string
     */
    public function prepareSpecialValueForOutput(string $field, $value, DataContainer $dc, array $config = [], bool $isRecursiveCall = false)
    {
        $value = StringUtil::deserialize($value);

        // Recursively apply logic to array
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $result = $this->prepareSpecialValueForOutput($field, $v, $dc, $config, true);

                if ($config['preserveEmptyArrayValues']) {
                    $value[$k] = $result;
                } else {
                    if (null !== $result && !empty($result)) {
                        $value[$k] = $result;
                    } else {
                        unset($value[$k]);
                    }
                }
            }

            // reset caches
            $this->optionsCache = null;

            return implode(', ', $value);
        }

        $table = $dc->table;

        if (!$config['skipDcaLoading']) {
            Controller::loadDataContainer($table);
            System::loadLanguageFile($table);
        }

        if (!isset($GLOBALS['TL_DCA'][$table]['fields'][$field]) || !is_array($GLOBALS['TL_DCA'][$table]['fields'][$field])) {
            return $value;
        }

        $data = $GLOBALS['TL_DCA'][$table]['fields'][$field];
        $reference = $data['reference'];
        $rgxp = $data['eval']['rgxp'];

        if (!$config['skipOptionCaching'] && null !== $this->optionsCache) {
            $options = $this->optionsCache;
        } else {
            $options = System::getContainer()->get('huh.utils.dca')->getConfigByArrayOrCallbackOrFunction($data, 'options', [$dc]);
            $this->optionsCache = $options;
        }

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
            $value = null;

            if (null !== $collection) {
                $result = $collection->fetchEach('name');
                $value = implode('', $result);
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

                        $fields[] = ($dca['label'][0] ?: $fieldName).': '.$this->prepareSpecialValueForOutput($fieldName, $fieldValue, $dc, $config);
                    }

                    $rows[] = '['.implode(', ', $fields).']';
                }

                $value = implode(', ', $rows);
            }
        } elseif (Validator::isBinaryUuid($value)) {
            $strPath = System::getContainer()->get('huh.utils.file')->getPathFromUuid($value);
            $value = $strPath ? Environment::get('url').'/'.$strPath : StringUtil::binToUuid($value);
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

        // reset caches
        if (!$isRecursiveCall) {
            $this->optionsCache = null;
        }

        // Convert special characters (see #1890)
        return specialchars($value);
    }

    public function escapeAllHtmlEntities($table, $field, $value)
    {
        if (!$value) {
            return $value;
        }

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
            $value = Request::clean($value, $data['eval']['decodeEntities'] ?? false, true);
        }

        return $value;
    }
}

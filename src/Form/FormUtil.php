<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Form;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\Date;
use Contao\Environment;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use Contao\Widget;
use HeimrichHannot\UtilsBundle\Model\CfgTagModel;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class FormUtil.
 *
 * @see https://heimrichhannot.github.io/contao-utils-bundle/HeimrichHannot/UtilsBundle/Form/FormUtil.html
 */
class FormUtil
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    /** @var array */
    protected $optionsCache;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container, ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
        $this->container = $container;
    }

    /**
     * Get a new widget instance based on given attributes from a Data Container array.
     *
     * @param string             $name   The field name in the form
     * @param array              $data   The field configuration array
     * @param mixed              $value  The field value
     * @param string             $dbName The field name in the database
     * @param string             $table  The table name in the database
     * @param DataContainer|null $dc     An optional DataContainer object
     * @param string             $mode   The contao mode, use FE or BE to get proper widget/form type
     *
     * @return Widget|null The new widget based on given attributes
     */
    public function getWidgetFromAttributes(string $name, array $data, $value = null, string $dbName = '', string $table = '', DataContainer $dc = null, string $mode = ''): ?Widget
    {
        if ('' === $mode) {
            $mode = System::getContainer()->get('huh.utils.container')->isFrontend() ? 'FE' : 'BE';
        }

        if ('hidden' === $data['inputType']) {
            $mode = 'FE';
        }

        $mode = strtoupper($mode);
        $mode = \in_array($mode, ['FE', 'BE']) ? $mode : 'FE';
        $class = 'FE' === $mode ? $GLOBALS['TL_FFL'][$data['inputType']] : $GLOBALS['BE_FFL'][$data['inputType']];
        /** @var $widget Widget */
        $widget = $this->framework->getAdapter(Widget::class);

        if (empty($class) || !class_exists($class)) {
            return null;
        }

        return new $class($widget->getAttributesFromDca($data, $name, $value, $dbName, $table, $dc));
    }

    /**
     * Prepares a special field's value. If an array is inserted, the function will call itself recursively.
     *
     * Possible config options:
     *
     * * preserveEmptyArrayValues -> preserves array values even if they're empty
     * * skipLocalization -> skips usage of "reference" array defined in the field's dca
     * * skipDcaLoading: boolean -> skip calling Controller::loadDataContainer on $dc->table
     * * skipOptionCaching -> skip caching options if $value is an array
     * * _dcaOverride: Array Set a custom dca from outside, which will be used instead of global dca value.
     *
     * @param $value
     *
     * @return string
     */
    public function prepareSpecialValueForOutput(string $field, $value, DataContainer $dc, array $config = [], bool $isRecursiveCall = false)
    {
        $value = StringUtil::deserialize($value);

        /** @var Controller $controller */
        $controller = $this->framework->getAdapter(Controller::class);

        /** @var System $system */
        $system = $this->framework->getAdapter(System::class);

        /** @var CfgTagModel $cfgTagModel */
        $cfgTagModel = $this->framework->getAdapter(CfgTagModel::class);

        $system->loadLanguageFile('default');

        // prepare data
        $table = $dc->table;

        if (!isset($config['skipDcaLoading']) || !$config['skipDcaLoading']) {
            $controller->loadDataContainer($table);
            $system->loadLanguageFile($table);
        }

        $arraySeparator = $config['arraySeparator'] ?? ', ';

        // dca can be overridden from outside
        if (isset($config['_dcaOverride']) && \is_array($config['_dcaOverride'])) {
            $data = $config['_dcaOverride'];
        } elseif (!isset($GLOBALS['TL_DCA'][$table]['fields'][$field]) || !\is_array($GLOBALS['TL_DCA'][$table]['fields'][$field])) {
            return $value;
        } else {
            $data = $GLOBALS['TL_DCA'][$table]['fields'][$field];
        }

        // multicolumneditor
        $mceFieldSeparator = $config['mceFieldSeparator'] ?? "\t";
        $mceRowSeparator = $config['mceRowSeparator'] ?? "\t\n";
        $skipMceFieldLabels = $config['skipMceFieldLabels'] ?? false;
        $skipMceFieldLabelFormatting = $config['skipMceFieldLabelFormatting'] ?? false;
        $skipMceFields = isset($config['skipMceFields']) && \is_array($config['skipMceFields']) ? $config['skipMceFields'] : [];
        $mceFields = isset($config['mceFields']) && \is_array($config['mceFields']) ? $config['mceFields'] : [];

        if ('multiColumnEditor' == $data['inputType']
            && $this->container->get('huh.utils.container')->isBundleActive('HeimrichHannot\MultiColumnEditorBundle\HeimrichHannotContaoMultiColumnEditorBundle')) {
            if (\is_array($value)) {
                $formatted = '';

                foreach ($value as $row) {
                    // new line - add "\t\n" after each line and not only "\n" to prevent outlook line break remover
                    $formatted .= $mceRowSeparator;

                    foreach ($row as $fieldName => $fieldValue) {
                        if (\in_array($fieldName, $skipMceFields) || (\is_array($mceFields) && !\in_array($fieldName, $mceFields))) {
                            continue;
                        }

                        $dca = $data['eval']['multiColumnEditor']['fields'][$fieldName];

                        $label = '';

                        if (!$skipMceFieldLabels) {
                            $label = ($dca['label'][0] ?: $fieldName).': ';

                            if ($skipMceFieldLabelFormatting) {
                                $label = $fieldName.': ';
                            }
                        }

                        // indent new line
                        $formatted .= $mceFieldSeparator.$label.$this->prepareSpecialValueForOutput($fieldName, $fieldValue, $dc, array_merge($config, [
                                '_dcaOverride' => $dca,
                            ]));
                    }
                }

                // new line - add "\t\n" after each line and not only "\n" to prevent outlook line break remover
                $formatted .= $mceRowSeparator;

                return $formatted;
            }
        }

        // inputUnit
        if ('inputUnit' == $data['inputType']) {
            $data = StringUtil::deserialize($value, true);

            if (!isset($data['value'])) {
                $data['value'] = '';
            }

            if (!isset($data['unit'])) {
                $data['unit'] = '';
            }

            return $data['value'].$arraySeparator.$data['unit'];
        }

        // Recursively apply logic to array
        if (\is_array($value)) {
            foreach ($value as $k => $v) {
                $result = $this->prepareSpecialValueForOutput($field, $v, $dc, $config, true);

                if (isset($config['preserveEmptyArrayValues']) && $config['preserveEmptyArrayValues']) {
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

            return implode($arraySeparator, $value);
        }

        $reference = null;

        if (isset($data['reference']) && (!isset($config['skipLocalization']) || !$config['skipLocalization'])) {
            $reference = $data['reference'];
        }

        $rgxp = null;

        if (isset($data['eval']['rgxp'])) {
            $rgxp = $data['eval']['rgxp'];
        }

        if ((!isset($config['skipOptionCaching']) || !$config['skipOptionCaching']) && null !== $this->optionsCache) {
            $options = $this->optionsCache;
        } else {
            try {
                $options = $this->container->get('huh.utils.dca')->getConfigByArrayOrCallbackOrFunction($data, 'options', [$dc]);
            } catch (\ErrorException $e) {
                $options = [];
            }

            $this->optionsCache = !\is_array($options) ? [] : $options;
        }

        // foreignKey
        if (isset($data['foreignKey'])) {
            list($foreignTable, $foreignField) = explode('.', $data['foreignKey']);

            if (null !== ($instance = $this->container->get('huh.utils.model')->findModelInstanceByPk($foreignTable, $value))) {
                $value = $instance->{$foreignField};
            }
        }

        if ('explanation' == $data['inputType']) {
            if (isset($data['eval']['text'])) {
                return $data['eval']['text'];
            }
        } elseif ('cfgTags' == $data['inputType']) {
            $collection = $cfgTagModel->findBy(['source=?', 'id = ?'], [$data['eval']['tagsManager'], $value]);
            $value = null;

            if (null !== $collection) {
                $result = $collection->fetchEach('name');
                $value = implode($arraySeparator, $result);
            }
        } elseif ('date' == $rgxp) {
            $value = Date::parse(Config::get('dateFormat'), $value);
        } elseif ('time' == $rgxp) {
            $value = Date::parse(Config::get('timeFormat'), $value);
        } elseif ('datim' == $rgxp) {
            $value = Date::parse(Config::get('datimFormat'), $value);
        } elseif (Validator::isBinaryUuid($value)) {
            $strPath = $this->container->get('huh.utils.file')->getPathFromUuid($value);
            $value = $strPath ? Environment::get('url').'/'.$strPath : StringUtil::binToUuid($value);
        } // Replace boolean checkbox value with "yes" and "no"
        else {
            if ((isset($data['eval']['isBoolean']) && $data['eval']['isBoolean']) || ('checkbox' == $data['inputType'] && !$data['eval']['multiple'])) {
                $value = ('' != $value) ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
            } elseif (\is_array($options) && array_is_assoc($options)) {
                $value = isset($options[$value]) ? $options[$value] : $value;
            }
        }

        if (\is_array($reference)) {
            $value = isset($reference[$value]) ? ((\is_array($reference[$value])) ? $reference[$value][0] : $reference[$value]) : $value;
        }

        if (isset($data['eval']['encrypt']) && $data['eval']['encrypt']) {
            list($encrypted, $iv) = explode('.', $value);

            $value = $this->container->get('huh.utils.encryption')->decrypt($encrypted, $iv);
        }

        // reset caches
        if (!$isRecursiveCall) {
            $this->optionsCache = null;
        }

        $value = Controller::replaceInsertTags($value);

        // Convert special characters (see #1890)
        return StringUtil::specialchars($value);
    }

    public function escapeAllHtmlEntities($table, $field, $value)
    {
        if (!$value) {
            return $value;
        }

        Controller::loadDataContainer($table);

        $data = $GLOBALS['TL_DCA'][$table]['fields'][$field];

        $preservedTags = isset($data['eval']['allowedTags']) ? $data['eval']['allowedTags'] : Config::get('allowedTags');

        if ($data['eval']['allowHtml'] || \strlen($data['eval']['rte']) || $data['eval']['preserveTags']) {
            // always decode entities if HTML is allowed
            $value = $this->container->get('huh.request')->cleanHtml($value, true, true, $preservedTags);
        } elseif (\is_array($data['options']) || isset($data['options_callback']) || isset($data['foreignKey'])) {
            // options should not be strict cleaned, as they might contain html tags like <strong>
            $value = $this->container->get('huh.request')->cleanHtml($value, true, true, $preservedTags);
        } else {
            $value = $this->container->get('huh.request')->clean($value, $data['eval']['decodeEntities'] ?? false, true);
        }

        return $value;
    }

    /**
     * Get an instance of Widget by passing fieldname and dca data.
     *
     * @param string     $fieldName     The field name
     * @param array      $dca           The DCA
     * @param array|null $value
     * @param string     $dbField       The database field name
     * @param string     $table         The table
     * @param null       $dataContainer object The data container
     *
     * @return Widget|null
     */
    public function getBackendFormField(string $fieldName, array $dca, $value = null, $dbField = '', $table = '', $dataContainer = null)
    {
        if (!($strClass = $GLOBALS['BE_FFL'][$dca['inputType']])) {
            return null;
        }

        return new $strClass(Widget::getAttributesFromDca($dca, $fieldName, $value, $dbField, $table, $dataContainer));
    }

    public function getModelDataAsNotificationTokens(array $data, string $prefix, DataContainer $dc, array $config = [])
    {
        $prefix = $prefix ?? 'form_';
        $skipRawValues = $config['skipRawValues'] ?? false;
        $rawValuePrefix = $config['rawValuePrefix'] ?? 'raw_';
        $skipFormattedValues = $config['skipFormattedValues'] ?? false;
        $formattedValuePrefix = $config['formattedValuePrefix'] ?? '';
        $skipFields = $config['skipFields'] ?? [];
        $restrictFields = $config['restrictFields'] ?? [];
        $formatOptions = $config['formatOptions'] ?? [];

        $result = [];

        // raw values
        if (!$skipRawValues) {
            foreach ($data as $field => $value) {
                if (empty($restrictFields) && \in_array($field, $skipFields)) {
                    continue;
                }

                if (!empty($restrictFields) && !\in_array($field, $restrictFields)) {
                    continue;
                }

                $result[$prefix.$rawValuePrefix.$field] = $value;
            }
        }

        // formatted values
        if (!$skipFormattedValues) {
            foreach ($data as $field => $value) {
                if (empty($restrictFields) && \in_array($field, $skipFields)) {
                    continue;
                }

                if (!empty($restrictFields) && !\in_array($field, $restrictFields)) {
                    continue;
                }

                $result[$prefix.$formattedValuePrefix.$field] = $this->prepareSpecialValueForOutput(
                    $field, $value, $dc, $formatOptions
                );
            }
        }

        return $result;
    }
}

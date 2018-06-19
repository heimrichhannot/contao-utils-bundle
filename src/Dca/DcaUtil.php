<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Dca;

use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\Database\Result;
use Contao\DataContainer;
use Contao\FrontendUser;
use Contao\Image;
use Contao\StringUtil;
use Contao\System;

class DcaUtil
{
    const PROPERTY_SESSION_ID = 'sessionID';
    const PROPERTY_AUTHOR = 'author';
    const PROPERTY_AUTHOR_TYPE = 'authorType';

    const AUTHOR_TYPE_NONE = 'none';
    const AUTHOR_TYPE_MEMBER = 'member';
    const AUTHOR_TYPE_USER = 'user';

    /** @var ContaoFrameworkInterface */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Get a contao backend modal edit link.
     *
     * @param string      $module Name of the module
     * @param int         $id     Id of the entity
     * @param string|null $label  The label text
     *
     * @return string The edit link
     */
    public function getEditLink(string $module, int $id, string $label = null): string
    {
        if (!$id) {
            return '';
        }

        $label = sprintf(specialchars($label ?: $GLOBALS['TL_LANG']['tl_content']['editalias'][1]), $id);

        return sprintf(
            ' <a href="contao/main.php?do=%s&amp;act=edit&amp;id=%s&amp;rt=%s" title="%s" style="padding-left: 5px; padding-top: 2px; display: inline-block;">%s</a>',
            $module,
            $id,
            System::getContainer()->get('security.csrf.token_manager')->getToken(System::getContainer()->getParameter('contao.csrf_token_name'))->getValue(),
            $label,
            Image::getHtml('alias.svg', $label, 'style="vertical-align:top"')
        );
    }

    /**
     * Get a contao backend modal edit link.
     *
     * @param string      $module Name of the module
     * @param int         $id     Id of the entity
     * @param string|null $label  The label text
     * @param string      $table  The dataContainer table
     * @param int         $width  The modal window width
     *
     * @return string The modal edit link
     */
    public function getModalEditLink(string $module, int $id, string $label = null, string $table = '', int $width = 768): string
    {
        if (!$id) {
            return '';
        }

        $label = sprintf(specialchars($label ?: $GLOBALS['TL_LANG']['tl_content']['editalias'][1]), $id);

        return sprintf(
            ' <a href="contao/main.php?do=%s&amp;act=edit&amp;id=%s%s&amp;popup=1&amp;nb=1&amp;rt=%s" title="%s" '
            .'style="padding-left: 5px; padding-top: 2px; display: inline-block;" onclick="Backend.openModalIframe({\'width\':\'%s\',\'title\':\'%s'.'\',\'url\':this.href});return false">%s</a>',
            $module,
            $id,
            ($table ? '&amp;table='.$table : ''),
            System::getContainer()->get('security.csrf.token_manager')->getToken(System::getContainer()->getParameter('contao.csrf_token_name'))->getValue(),
            $label,
            $width,
            $label,
            \Image::getHtml('alias.svg', $label, 'style="vertical-align:top"')
        );
    }

    /**
     * Get a contao backend modal archive edit link.
     *
     * @param string      $module Name of the module
     * @param int         $id     Id of the entity
     * @param string      $table  The dataContainer table
     * @param string|null $label  The label text
     * @param int         $width  The modal window width
     *
     * @return string The modal archive edit link
     */
    public function getArchiveModalEditLink(string $module, int $id, string $table, string $label = null, int $width = 768): string
    {
        if (!$id) {
            return '';
        }

        $label = sprintf(specialchars($label ?: $GLOBALS['TL_LANG']['tl_content']['editalias'][1]), $id);

        return sprintf(
            ' <a href="contao/main.php?do=%s&amp;id=%s&amp;table=%s&amp;popup=1&amp;nb=1&amp;rt=%s" title="%s" '
            .'style="padding-left:3px; float: right" onclick="Backend.openModalIframe({\'width\':\'%s\',\'title\':\'%s'.'\',\'url\':this.href});return false">%s</a>',
            $module,
            $id,
            $table,
            System::getContainer()->get('security.csrf.token_manager')->getToken(System::getContainer()->getParameter('contao.csrf_token_name'))->getValue(),
            $label,
            $width,
            $label,
            Image::getHtml('alias.svg', $label, 'style="vertical-align:top"')
        );
    }

    /**
     * Set initial $varData from dca.
     *
     * @param string $strTable Dca table name
     * @param mixed  $varData  Object or array
     *
     * @return mixed Object or array with the default values
     */
    public function setDefaultsFromDca($strTable, $varData = null)
    {
        \Controller::loadDataContainer($strTable);
        if (empty($GLOBALS['TL_DCA'][$strTable])) {
            return $varData;
        }
        // Get all default values for the new entry
        foreach ($GLOBALS['TL_DCA'][$strTable]['fields'] as $k => $v) {
            // Use array_key_exists here (see #5252)
            if (array_key_exists('default', $v)) {
                if (is_object($varData)) {
                    $varData->{$k} = is_array($v['default']) ? serialize($v['default']) : $v['default'];
                    // Encrypt the default value (see #3740)
                    if ($GLOBALS['TL_DCA'][$strTable]['fields'][$k]['eval']['encrypt']) {
                        $varData->{$k} = System::getContainer()->get('huh.utils.encryption')->encrypt($varData->{$k});
                    }
                } else {
                    if (null === $varData) {
                        $varData = [];
                    }
                    if (is_array($varData)) {
                        $varData[$k] = is_array($v['default']) ? serialize($v['default']) : $v['default'];
                        // Encrypt the default value (see #3740)
                        if ($GLOBALS['TL_DCA'][$strTable]['fields'][$k]['eval']['encrypt']) {
                            $varData[$k] = System::getContainer()->get('huh.utils.encryption')->encrypt($varData[$k]);
                        }
                    }
                }
            }
        }

        return $varData;
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
     * @throws \ErrorException When the callback has not enough context, for example no BackendUser is available
     *
     * @return mixed|null The value retrieved in the way mentioned above or null
     */
    public function getConfigByArrayOrCallbackOrFunction(array $array, $property, array $arguments = [])
    {
        if (isset($array[$property])) {
            return $array[$property];
        }

        if (!isset($array[$property.'_callback'])) {
            return null;
        }

        if (is_array($array[$property.'_callback'])) {
            $callback = $array[$property.'_callback'];

            if (!isset($callback[0]) || !isset($callback[1]) || !class_exists($callback[0])) {
                return null;
            }

            $instance = Controller::importStatic($callback[0]);

            if (!method_exists($instance, $callback[1])) {
                return null;
            }

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
            return null;
        }

        $this->framework->createInstance(Database::class)->prepare("UPDATE $dc->table SET dateAdded=? WHERE id=? AND dateAdded = 0")->execute(time(), $dc->id);
    }

    /**
     * Sets the current date as the date added -> usually used on copy.
     *
     * @param $insertId
     * @param DataContainer $dc
     */
    public function setDateAddedOnCopy($insertId, DataContainer $dc)
    {
        $modelUtil = System::getContainer()->get('huh.utils.model');

        if (null === $dc || null === ($model = $modelUtil->findModelInstanceByPk($dc->table, $insertId)) || $model->dateAdded > 0) {
            return null;
        }

        $this->framework->createInstance(Database::class)->prepare("UPDATE $dc->table SET dateAdded=? WHERE id=? AND dateAdded = 0")->execute(time(), $insertId);
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
            if (isset($options['inputTypes']) && is_array($options['inputTypes']) && !empty($options['inputTypes']) && !in_array($data['inputType'], $options['inputTypes'], true)) {
                continue;
            }

            // restrict to certain dca eval
            if (isset($options['evalConditions']) && is_array($options['evalConditions']) && !empty($options['evalConditions'])) {
                foreach ($options['evalConditions'] as $key => $value) {
                    if ($data['eval'][$key] !== $value) {
                        continue 2;
                    }
                }
            }

            if (isset($options['localizeLabels']) && !$options['localizeLabels']) {
                $fields[$name] = $name;
            } else {
                $fields[$name] = ($data['label'][0] ?: $name).($data['label'][0] ? ' <span style="display: inline; color:#999; padding-left:3px">['.$name.']</span>' : '');
            }
        }

        if (!isset($options['skipSorting']) || !$options['skipSorting']) {
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
                'eval' => ['tl_class' => 'w50', 'submitOnChange' => true, 'isOverrideSelector' => true],
                'sql' => "char(1) NOT NULL default ''",
            ];

            if (isset($options['checkboxDcaEvalOverride']) && is_array($options['checkboxDcaEvalOverride'])) {
                $destinationDca['fields'][$overrideFieldname]['eval'] = array_merge($destinationDca['fields'][$overrideFieldname]['eval'], $options['checkboxDcaEvalOverride']);
            }

            // important: nested selectors need to be in reversed order -> see DC_Table::getPalette()
            $destinationDca['palettes']['__selector__'] = array_merge([$overrideFieldname], is_array($destinationDca['palettes']['__selector__']) ? $destinationDca['palettes']['__selector__'] : []);

            // copy field
            $destinationDca['fields'][$field] = $sourceDca['fields'][$field];

            // subpalette
            $destinationDca['subpalettes'][$overrideFieldname] = $field;

            if (!isset($options['skipLocalization']) || !$options['skipLocalization']) {
                $GLOBALS['TL_LANG'][$destinationTable][$overrideFieldname] = [
                    System::getContainer()->get('translator')->trans('huh.utils.misc.override.label', [
                        '%fieldname%' => $GLOBALS['TL_DCA'][$sourceTable]['fields'][$field]['label'][0] ?: $field,
                    ]),
                    System::getContainer()->get('translator')->trans('huh.utils.misc.override.desc', [
                        '%fieldname%' => $GLOBALS['TL_DCA'][$sourceTable]['fields'][$field]['label'][0] ?: $field,
                    ]),
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
            } elseif ($instance instanceof \Model || is_object($instance)) {
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
    public function flattenPaletteForSubEntities(string $table, array $overridableFields)
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

    /**
     * Generate an alias.
     *
     * @param mixed  $alias       The current alias (if available)
     * @param int    $id          The entity's id
     * @param string $table       The entity's table
     * @param string $title       The value to use as a base for the alias
     * @param bool   $keepUmlauts Set to true if German umlauts should be kept
     *
     * @throws \Exception
     *
     * @return string
     */
    public function generateAlias(string $alias, int $id, string $table, string $title, bool $keepUmlauts = true)
    {
        $autoAlias = false;

        // Generate alias if there is none
        if (empty($alias)) {
            $autoAlias = true;
            $alias = StringUtil::generateAlias($title);
        }

        if (!$keepUmlauts) {
            $alias = preg_replace(['/ä/i', '/ö/i', '/ü/i', '/ß/i'], ['ae', 'oe', 'ue', 'ss'], $alias);
        }

        /**
         * @var Result
         */
        $existingAlias = $this->framework->createInstance(Database::class)->getInstance()->prepare("SELECT id FROM $table WHERE alias=?")->execute($alias);

        if ($existingAlias->id == $id) {
            return $alias;
        }

        // Check whether the alias exists
        if ($existingAlias->numRows > 0 && !$autoAlias) {
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $alias));
        }

        // Add ID to alias
        if ($existingAlias->numRows && $existingAlias->id != $id && $autoAlias || !$alias) {
            $alias .= '-'.$id;
        }

        return $alias;
    }

    public function addAuthorFieldAndCallback(string $table)
    {
        Controller::loadDataContainer($table);

        // callbacks
        $GLOBALS['TL_DCA'][$table]['config']['oncreate_callback']['setAuthorIDOnCreate'] = ['huh.utils.dca', 'setAuthorIDOnCreate'];
        $GLOBALS['TL_DCA'][$table]['config']['onload_callback']['modifyAuthorPaletteOnLoad'] = ['huh.utils.dca', 'modifyAuthorPaletteOnLoad', true];

        // fields
        $GLOBALS['TL_DCA'][$table]['fields'][static::PROPERTY_AUTHOR_TYPE] = [
            'label' => &$GLOBALS['TL_LANG']['MSC']['utilsBundle']['authorType'],
            'exclude' => true,
            'filter' => true,
            'default' => static::AUTHOR_TYPE_NONE,
            'inputType' => 'select',
            'options' => [
                static::AUTHOR_TYPE_NONE,
                static::AUTHOR_TYPE_MEMBER,
                static::AUTHOR_TYPE_USER,
            ],
            'reference' => $GLOBALS['TL_LANG']['MSC']['utilsBundle']['authorType'],
            'eval' => ['doNotCopy' => true, 'submitOnChange' => true, 'mandatory' => true, 'tl_class' => 'w50 clr'],
            'sql' => "varchar(255) NOT NULL default 'none'",
        ];

        $GLOBALS['TL_DCA'][$table]['fields'][static::PROPERTY_AUTHOR] = [
            'label' => &$GLOBALS['TL_LANG']['MSC']['utilsBundle']['author'],
            'exclude' => true,
            'search' => true,
            'filter' => true,
            'inputType' => 'select',
            'options_callback' => function () {
                return \Contao\System::getContainer()->get('huh.utils.choice.model_instance')->getCachedChoices([
                    'dataContainer' => 'tl_member',
                    'labelPattern' => '%firstname% %lastname% (ID %id%)',
                ]);
            },
            'eval' => [
                'doNotCopy' => true,
                'chosen' => true,
                'includeBlankOption' => true,
                'tl_class' => 'w50',
            ],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ];
    }

    public function setAuthorIDOnCreate(string $table, int $id, array $row, DataContainer $dc)
    {
        $model = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($table, $id);
        /** @var Database $db */
        $db = $this->framework->createInstance(Database::class);

        if (null === $model
            || !$db->fieldExists(static::PROPERTY_AUTHOR_TYPE, $table)
            || !$db->fieldExists(static::PROPERTY_AUTHOR, $table)) {
            return false;
        }

        if (System::getContainer()->get('huh.utils.container')->isFrontend()) {
            if (FE_USER_LOGGED_IN) {
                $model->{static::PROPERTY_AUTHOR_TYPE} = static::AUTHOR_TYPE_MEMBER;
                $model->{static::PROPERTY_AUTHOR} = $this->framework->getAdapter(FrontendUser::class)->getInstance()->id;
                $model->save();
            }
        } else {
            $model->{static::PROPERTY_AUTHOR_TYPE} = static::AUTHOR_TYPE_USER;
            $model->{static::PROPERTY_AUTHOR} = $this->framework->getAdapter(BackendUser::class)->getInstance()->id;
            $model->save();
        }
    }

    public function modifyAuthorPaletteOnLoad(DataContainer $dc)
    {
        if (!System::getContainer()->get('huh.utils.container')->isBackend()) {
            return false;
        }

        if (null === $dc || !$dc->id || !$dc->table) {
            return false;
        }

        if (null === ($model = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($dc->table, $dc->id))) {
            return false;
        }

        $dca = &$GLOBALS['TL_DCA'][$dc->table];

        // author handling
        if ($model->{static::PROPERTY_AUTHOR_TYPE} == static::AUTHOR_TYPE_NONE) {
            unset($dca['fields']['author']);
        }

        if ($model->{static::PROPERTY_AUTHOR_TYPE} == static::AUTHOR_TYPE_USER) {
            $dca['fields']['author']['options_callback'] = function () {
                return \Contao\System::getContainer()->get('huh.utils.choice.model_instance')->getCachedChoices([
                    'dataContainer' => 'tl_user',
                    'labelPattern' => '%name% (ID %id%)',
                ]);
            };
        }
    }

    /**
     * @return array
     */
    public function getDataContainers()
    {
        $dca = [];
        foreach ($GLOBALS['BE_MOD'] as $arrSection) {
            foreach ($arrSection as $strModule => $arrModule) {
                foreach ($arrModule as $strKey => $varValue) {
                    if (is_array($arrModule['tables'])) {
                        $dca = array_merge($dca, $arrModule['tables']);
                    }
                }
            }
        }
        $dca = array_unique($dca);
        asort($dca);

        return array_values($dca);
    }

    /**
     * @param bool $includeNotificationCenterPlusTokens
     *
     * @return array
     */
    public function getNewNotificationTypeArray($includeNotificationCenterPlusTokens = false)
    {
        $type = [
            'recipients' => ['admin_email'],
            'email_subject' => ['admin_email'],
            'email_text' => ['admin_email'],
            'email_html' => ['admin_email'],
            'file_name' => ['admin_email'],
            'file_content' => ['admin_email'],
            'email_sender_name' => ['admin_email'],
            'email_sender_address' => ['admin_email'],
            'email_recipient_cc' => ['admin_email'],
            'email_recipient_bcc' => ['admin_email'],
            'email_replyTo' => ['admin_email'],
            'attachment_tokens' => [],
        ];

        if ($includeNotificationCenterPlusTokens) {
            foreach ($type as $field => $tokens) {
                $type[$field] = array_unique(array_merge([
                    'env_*',
                    'page_*',
                    'user_*',
                    'date',
                    'last_update',
                ], $tokens));
            }
        }

        return $type;
    }

    /**
     * Adds an alias field to the dca and to the desired palettes.
     *
     * @param       $dca
     * @param       $generateAliasCallback array The callback to call for generating the alias
     * @param       $paletteField          String The field after which to insert the alias field in the palettes
     * @param array $palettes              The palettes in which to insert the field
     */
    public function addAliasToDca(string $dca, array $generateAliasCallback, string $paletteField, array $palettes = ['default'])
    {
        Controller::loadDataContainer($dca);

        $arrDca = &$GLOBALS['TL_DCA'][$dca];

        // add to palettes
        foreach ($palettes as $strPalette) {
            $arrDca['palettes'][$strPalette] = str_replace($paletteField.',', $paletteField.',alias,', $arrDca['palettes'][$strPalette]);
        }

        // add field
        $arrDca['fields']['alias'] = [
            'label' => &$GLOBALS['TL_LANG']['MSC']['alias'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'alias', 'unique' => true, 'maxlength' => 128, 'tl_class' => 'w50'],
            'save_callback' => [$generateAliasCallback],
            'sql' => "varchar(128) COLLATE utf8_bin NOT NULL default ''",
        ];
    }

    /**
     * @param $strField
     * @param $strTable
     *
     * @return mixed
     */
    public function getLocalizedFieldName($strField, $strTable)
    {
        Controller::loadDataContainer($strTable);
        System::loadLanguageFile($strTable);

        return $GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['label'][0] ?: $strField;
    }
}

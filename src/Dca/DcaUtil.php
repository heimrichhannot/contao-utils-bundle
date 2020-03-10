<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Dca;

use Contao\BackendUser;
use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\Database\Result;
use Contao\DataContainer;
use Contao\DcaExtractor;
use Contao\DiffRenderer;
use Contao\Environment;
use Contao\FrontendUser;
use Contao\Image;
use Contao\Input;
use Contao\Model;
use Contao\RequestToken;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use HeimrichHannot\UtilsBundle\Driver\DC_Table_Utils;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container, ContaoFrameworkInterface $framework)
    {
        $this->container = $container;
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
        $url = $this->container->get('huh.utils.url')->getCurrentUrl([
            'skipParams' => true,
        ]);

        if (!$id) {
            return '';
        }

        $label = sprintf(StringUtil::specialchars($label ?: $GLOBALS['TL_LANG']['tl_content']['editalias'][1]), $id);

        return sprintf(
            ' <a href="'.$url.'?do=%s&amp;act=edit&amp;id=%s&amp;rt=%s" title="%s" style="padding-left: 5px; padding-top: 2px; display: inline-block;">%s</a>',
            $module,
            $id,
            $this->container->get('security.csrf.token_manager')->getToken($this->container->getParameter('contao.csrf_token_name'))->getValue(),
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
     *
     * @deprecated Use DcaUtil::getPopupWizardLink() instead
     */
    public function getModalEditLink(string $module, int $id, string $label = null, string $table = '', int $width = 1024): string
    {
        $url = $this->container->get('huh.utils.url')->getCurrentUrl([
            'skipParams' => true,
        ]);

        if (!$id) {
            return '';
        }

        $label = sprintf(StringUtil::specialchars($label ?: $GLOBALS['TL_LANG']['tl_content']['editalias'][1]), $id);

        return sprintf(
            ' <a href="'.$url.'?do=%s&amp;act=edit&amp;id=%s%s&amp;popup=1&amp;nb=1&amp;rt=%s" title="%s" '
            .'style="padding-left: 5px; padding-top: 2px; display: inline-block;" onclick="Backend.openModalIframe({\'width\':%s,\'title\':\'%s'.'\',\'url\':this.href});return false">%s</a>',
            $module,
            $id,
            ($table ? '&amp;table='.$table : ''),
            $this->container->get('security.csrf.token_manager')->getToken($this->container->getParameter('contao.csrf_token_name'))->getValue(),
            $label,
            $width,
            $label,
            Image::getHtml('alias.svg', $label, 'style="vertical-align:top"')
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
     *
     * @deprecated Use DcaUtil::getPopupWizardLink() instead
     */
    public function getArchiveModalEditLink(string $module, int $id, string $table, string $label = null, int $width = 1024): string
    {
        $url = $this->container->get('huh.utils.url')->getCurrentUrl([
            'skipParams' => true,
        ]);

        if (!$id) {
            return '';
        }

        $label = sprintf(StringUtil::specialchars($label ?: $GLOBALS['TL_LANG']['tl_content']['editalias'][1]), $id);

        return sprintf(
            ' <a href="'.$url.'?do=%s&amp;id=%s&amp;table=%s&amp;popup=1&amp;nb=1&amp;rt=%s" title="%s" '
            .'style="padding-left:3px; float: right" onclick="Backend.openModalIframe({\'width\':\'%s\',\'title\':\'%s'.'\',\'url\':this.href});return false">%s</a>',
            $module,
            $id,
            $table,
            $this->container->get('security.csrf.token_manager')->getToken($this->container->getParameter('contao.csrf_token_name'))->getValue(),
            $label,
            $width,
            $label,
            Image::getHtml('alias.svg', $label, 'style="vertical-align:top"')
        );
    }

    /**
     * Get a contao backend popup link.
     *
     * @param string $href (e.g. do=news&id=1000&table=tl_news)
     *
     * @return string
     */
    public function getPopupWizardLink(string $href, array $options = [])
    {
        $requestToken = $this->container->get('security.csrf.token_manager')->getToken(
            $this->container->getParameter('contao.csrf_token_name')
        )->getValue();

        $href = Environment::get('url').parse_url(Environment::get('uri'), PHP_URL_PATH).'?'.ltrim($href, '?');
        $href = $this->container->get('huh.utils.url')->addQueryString('popup=1&nb=1&rt='.$requestToken, $href);

        // title
        if (!isset($options['title']) || !$options['title']) {
            $title = $GLOBALS['TL_LANG']['tl_content']['edit'][0];
        } else {
            $title = StringUtil::specialchars($options['title']);
        }

        // style
        $style = !isset($options['style']) ? 'padding-left: 5px; padding-top: 2px; display: inline-block;' : $options['style'];

        // onclick
        if (!isset($options['onclick']) || !$options['onclick']) {
            $popupWidth = !isset($options['popupWidth']) || !$options['popupWidth'] ? 991 : $options['popupWidth'];
            $popupTitle = !isset($options['popupTitle']) || !$options['popupTitle'] ? $title : $options['popupTitle'];

            $onclick = sprintf(
                'onclick="Backend.openModalIframe({\'width\':%s,\'title\':\'%s'.'\',\'url\':this.href});return false"',
                $popupWidth,
                $popupTitle
            );
        } else {
            $onclick = $options['onclick'];
        }

        // icon
        $icon = !isset($options['icon']) || !$options['icon'] ? 'alias.svg' : $options['icon'];

        return sprintf(
            '<a href="%s" title="%s" style="%s" %s>%s</a>',
            $href,
            $title,
            $style,
            $onclick,
            Image::getHtml($icon, $title, 'style="vertical-align:top"')
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
        $this->framework->getAdapter(Controller::class)->loadDataContainer($strTable);

        if (empty($GLOBALS['TL_DCA'][$strTable])) {
            return $varData;
        }
        // Get all default values for the new entry
        foreach ($GLOBALS['TL_DCA'][$strTable]['fields'] as $k => $v) {
            // Use array_key_exists here (see #5252)
            if (\array_key_exists('default', $v)) {
                if (\is_object($varData)) {
                    $varData->{$k} = \is_array($v['default']) ? serialize($v['default']) : $v['default'];
                    // Encrypt the default value (see #3740)
                    if ($GLOBALS['TL_DCA'][$strTable]['fields'][$k]['eval']['encrypt']) {
                        $varData->{$k} = $this->container->get('huh.utils.encryption')->encrypt($varData->{$k});
                    }
                } else {
                    if (null === $varData) {
                        $varData = [];
                    }

                    if (\is_array($varData)) {
                        $varData[$k] = \is_array($v['default']) ? serialize($v['default']) : $v['default'];
                        // Encrypt the default value (see #3740)
                        if ($GLOBALS['TL_DCA'][$strTable]['fields'][$k]['eval']['encrypt']) {
                            $varData[$k] = $this->container->get('huh.utils.encryption')->encrypt($varData[$k]);
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
     * 2. The value retrieved by $array[$property . '_callback'] which is a callback array like ['Class', 'method'] or ['service.id', 'method']
     * 3. The value retrieved by $array[$property . '_callback'] which is a function closure array like ['Class', 'method']
     *
     * @param $property
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

        if (\is_array($array[$property.'_callback'])) {
            $callback = $array[$property.'_callback'];

            if (!isset($callback[0]) || !isset($callback[1])) {
                return null;
            }

            try {
                $instance = Controller::importStatic($callback[0]);
            } catch (\Exception $e) {
                return null;
            }

            if (!method_exists($instance, $callback[1])) {
                return null;
            }

            return \call_user_func_array([$instance, $callback[1]], $arguments);
        } elseif (\is_callable($array[$property.'_callback'])) {
            return \call_user_func_array($array[$property.'_callback'], $arguments);
        }

        return null;
    }

    /**
     * Sets the current date as the date added -> usually used on submit.
     */
    public function setDateAdded(DataContainer $dc)
    {
        $modelUtil = $this->container->get('huh.utils.model');

        if (null === $dc || null === ($model = $modelUtil->findModelInstanceByPk($dc->table, $dc->id)) || $model->dateAdded > 0) {
            return null;
        }

        $this->framework->createInstance(Database::class)->prepare("UPDATE $dc->table SET dateAdded=? WHERE id=? AND dateAdded = 0")->execute(time(), $dc->id);
    }

    /**
     * Sets the current date as the date added -> usually used on copy.
     *
     * @param $insertId
     */
    public function setDateAddedOnCopy($insertId, DataContainer $dc)
    {
        $modelUtil = $this->container->get('huh.utils.model');

        if (null === $dc || null === ($model = $modelUtil->findModelInstanceByPk($dc->table, $insertId)) || $model->dateAdded > 0) {
            return null;
        }

        $this->framework->createInstance(Database::class)->prepare("UPDATE $dc->table SET dateAdded=? WHERE id=? AND dateAdded = 0")->execute(time(), $insertId);
    }

    /**
     * Returns a list of fields as an option array for dca fields.
     *
     * Possible options:
     * - array inputTypes Restrict to certain input types
     * - array evalConditions restrict to certain dca eval
     * - bool localizeLabels
     * - bool skipSorting
     */
    public function getFields(string $table, array $options = []): array
    {
        $fields = [];

        if (!$table) {
            return $fields;
        }

        $this->framework->getAdapter(Controller::class)->loadDataContainer($table);
        System::loadLanguageFile($table);

        if (!isset($GLOBALS['TL_DCA'][$table]['fields'])) {
            return $fields;
        }

        foreach ($GLOBALS['TL_DCA'][$table]['fields'] as $name => $data) {
            // restrict to certain input types
            if (isset($options['inputTypes']) && \is_array($options['inputTypes']) && !empty($options['inputTypes']) && !\in_array($data['inputType'], $options['inputTypes'])) {
                continue;
            }

            // restrict to certain dca eval
            if (isset($options['evalConditions']) && \is_array($options['evalConditions']) && !empty($options['evalConditions'])) {
                foreach ($options['evalConditions'] as $key => $value) {
                    if ($data['eval'][$key] !== $value) {
                        continue 2;
                    }
                }
            }

            if (isset($options['localizeLabels']) && !$options['localizeLabels']) {
                $fields[$name] = $name;
            } else {
                $label = $name;

                if (isset($data['label'][0]) && $data['label'][0]) {
                    $label .= ' <span style="display: inline; color:#999; padding-left:3px">['.$data['label'][0].']</span>';
                }

                $fields[$name] = $label;
            }
        }

        if (!isset($options['skipSorting']) || !$options['skipSorting']) {
            asort($fields);
        }

        return $fields;
    }

    /**
     * Adds an override selector to every field in $fields to the dca associated with $destinationTable.
     */
    public function addOverridableFields(array $fields, string $sourceTable, string $destinationTable, array $options = [])
    {
        $this->framework->getAdapter(Controller::class)->loadDataContainer($sourceTable);
        System::loadLanguageFile($sourceTable);
        $sourceDca = $GLOBALS['TL_DCA'][$sourceTable];

        $this->framework->getAdapter(Controller::class)->loadDataContainer($destinationTable);
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

            if (isset($options['checkboxDcaEvalOverride']) && \is_array($options['checkboxDcaEvalOverride'])) {
                $destinationDca['fields'][$overrideFieldname]['eval'] = array_merge($destinationDca['fields'][$overrideFieldname]['eval'], $options['checkboxDcaEvalOverride']);
            }

            // important: nested selectors need to be in reversed order -> see DC_Table::getPalette()
            $destinationDca['palettes']['__selector__'] = array_merge([$overrideFieldname], \is_array($destinationDca['palettes']['__selector__']) ? $destinationDca['palettes']['__selector__'] : []);

            // copy field
            $destinationDca['fields'][$field] = $sourceDca['fields'][$field];

            // subpalette
            $destinationDca['subpalettes'][$overrideFieldname] = $field;

            if (!isset($options['skipLocalization']) || !$options['skipLocalization']) {
                $GLOBALS['TL_LANG'][$destinationTable][$overrideFieldname] = [
                    $this->container->get('translator')->trans('huh.utils.misc.override.label', [
                        '%fieldname%' => $GLOBALS['TL_DCA'][$sourceTable]['fields'][$field]['label'][0] ?: $field,
                    ]),
                    $this->container->get('translator')->trans('huh.utils.misc.override.desc', [
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
            if (\is_array($instance)) {
                if (null !== ($objInstance = $this->container->get('huh.utils.model')->findModelInstanceByPk($instance[0], $instance[1]))) {
                    $preparedInstances[] = $objInstance;
                }
            } elseif ($instance instanceof Model || \is_object($instance)) {
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
     */
    public function flattenPaletteForSubEntities(string $table, array $overridableFields)
    {
        $this->framework->getAdapter(Controller::class)->loadDataContainer($table);

        $dca = &$GLOBALS['TL_DCA'][$table];
        $arrayUtil = $this->container->get('huh.utils.array');

        // palette
        foreach ($overridableFields as $field) {
            if (true === $dca['fields'][$field]['eval']['submitOnChange']) {
                unset($dca['fields'][$field]['eval']['submitOnChange']);

                if (\in_array($field, $dca['palettes']['__selector__'])) {
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

    public function addAuthorFieldAndCallback(string $table, string $fieldPrefix = '')
    {
        $this->framework->getAdapter(Controller::class)->loadDataContainer($table);

        // callbacks
        $GLOBALS['TL_DCA'][$table]['config']['oncreate_callback']['setAuthorIDOnCreate'] = ['huh.utils.dca', 'setAuthorIDOnCreate'];
        $GLOBALS['TL_DCA'][$table]['config']['onload_callback']['modifyAuthorPaletteOnLoad'] = ['huh.utils.dca', 'modifyAuthorPaletteOnLoad', true];

        // fields
        $GLOBALS['TL_DCA'][$table]['fields'][$fieldPrefix ? $fieldPrefix.ucfirst(static::PROPERTY_AUTHOR_TYPE) : static::PROPERTY_AUTHOR_TYPE] = [
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

        $GLOBALS['TL_DCA'][$table]['fields'][$fieldPrefix ? $fieldPrefix.ucfirst(static::PROPERTY_AUTHOR) : static::PROPERTY_AUTHOR] = [
            'label' => &$GLOBALS['TL_LANG']['MSC']['utilsBundle']['author'],
            'exclude' => true,
            'search' => true,
            'filter' => true,
            'inputType' => 'select',
            'options_callback' => function () {
                return $this->container->get('huh.utils.choice.model_instance')->getCachedChoices([
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
        $model = $this->container->get('huh.utils.model')->findModelInstanceByPk($table, $id);
        /** @var Database $db */
        $db = $this->framework->createInstance(Database::class);

        if (null === $model
            || !$db->fieldExists(static::PROPERTY_AUTHOR_TYPE, $table)
            || !$db->fieldExists(static::PROPERTY_AUTHOR, $table)) {
            return false;
        }

        if ($this->container->get('huh.utils.container')->isFrontend()) {
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
        if (!$this->container->get('huh.utils.container')->isBackend()) {
            return false;
        }

        if (null === $dc || !$dc->id || !$dc->table) {
            return false;
        }

        if (null === ($model = $this->container->get('huh.utils.model')->findModelInstanceByPk($dc->table, $dc->id))) {
            return false;
        }

        $dca = &$GLOBALS['TL_DCA'][$dc->table];

        // author handling
        if ($model->{static::PROPERTY_AUTHOR_TYPE} == static::AUTHOR_TYPE_NONE) {
            unset($dca['fields']['author']);
        }

        if ($model->{static::PROPERTY_AUTHOR_TYPE} == static::AUTHOR_TYPE_USER) {
            $dca['fields']['author']['options_callback'] = function () {
                return $this->container->get('huh.utils.choice.model_instance')->getCachedChoices([
                    'dataContainer' => 'tl_user',
                    'labelPattern' => '%name% (ID %id%)',
                ]);
            };
        }
    }

    /**
     * Returns (nearly) all registered datacontainers as array.
     *
     * Options:
     * - bool onlyTableType: Return only table data containers
     *
     * @return array
     */
    public function getDataContainers(array $options = [])
    {
        $dcaTables = $this->framework->createInstance(Database::class)->listTables();

        if (isset($options['onlyTableType']) && true === $options['onlyTableType']) {
            return $dcaTables;
        }

        foreach ($GLOBALS['BE_MOD'] as $arrSection) {
            foreach ($arrSection as $strModule => $arrModule) {
                foreach ($arrModule as $strKey => $varValue) {
                    if (\is_array($arrModule['tables'])) {
                        $dcaTables = array_merge($dcaTables, $arrModule['tables']);
                    }
                }
            }
        }
        $dcaTables = array_unique($dcaTables);
        asort($dcaTables);

        return array_values($dcaTables);
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

    public function activateNotificationType($strGroup, $strType, $arrType)
    {
        $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'] = array_merge_recursive(
            (array) $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'],
            [
                $strGroup => [
                    $strType => $arrType,
                ],
            ]
        );
    }

    /**
     * Adds an alias field to the dca and to the desired palettes.
     *
     * @param       $dca
     * @param       $generateAliasCallback mixed The callback to call for generating the alias
     * @param       $paletteField          String The field after which to insert the alias field in the palettes
     * @param array $palettes              The palettes in which to insert the field
     */
    public function addAliasToDca(string $dca, $generateAliasCallback, string $paletteField, array $palettes = ['default'])
    {
        Controller::loadDataContainer($dca);

        $arrDca = &$GLOBALS['TL_DCA'][$dca];

        // add to palettes
        foreach ($palettes as $strPalette) {
            $arrDca['palettes'][$strPalette] = preg_replace('/('.$paletteField.')(;|,)/', '$1,alias$2', $arrDca['palettes'][$strPalette]);
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

    /**
     * Load a data container in a testable way.
     */
    public function loadDc(string $table)
    {
        if (!isset($GLOBALS['TL_DCA'][$table]) || null === $GLOBALS['TL_DCA'][$table]) {
            /** @var Controller $controller */
            $controller = $this->framework->getAdapter(Controller::class);

            $controller->loadDataContainer($table);
        }
    }

    /**
     * Load a language file in a testable way.
     */
    public function loadLanguageFile(string $table)
    {
        /** @var System $system */
        $system = $this->framework->getAdapter(System::class);

        $system->loadLanguageFile($table);
    }

    public function isDcMultilingual(string $table)
    {
        $this->loadDc($table);

        $bundleName = 'Terminal42\DcMultilingualBundle\Terminal42DcMultilingualBundle';

        return isset($GLOBALS['TL_DCA'][$table]['config']['dataContainer']) &&
            'Multilingual' === $GLOBALS['TL_DCA'][$table]['config']['dataContainer'] &&
            $this->container->get('huh.utils.container')->isBundleActive($bundleName);
    }

    public function isDcMultilingual3()
    {
        return class_exists('Terminal42\DcMultilingualBundle\Model\Multilingual') &&
            !method_exists('Terminal42\DcMultilingualBundle\Model\Multilingual', 'createModelFromDbResult');
    }

    public function generateDcOperationsButtons($row, $table, $rootIds = [], $options = [])
    {
        $return = '';

        // Edit multiple
        if ('select' == Input::get('act')) {
            $return .= '<input type="checkbox" name="IDS[]" id="ids_'.$row['id'].'" class="tl_tree_checkbox" value="'.$row['id'].'">';
        } // Regular buttons
        else {
            $return .= $this->doGenerateDcOperationsButtons($row, $table, $rootIds, false, null, $options);

            // no picker support due to DataContainer not being extensible
        }

        return $return;
    }

    public function doGenerateDcOperationsButtons($arrRow, $strTable, $arrRootIds = [], $blnCircularReference = false, $arrChildRecordIds = null, $options = [])
    {
        if (empty($GLOBALS['TL_DCA'][$strTable]['list']['operations'])) {
            return '';
        }

        $return = '';

        $skipOperations = $options['skipOperations'] ?? [];
        $operations = $options['operations'] ?? array_keys($GLOBALS['TL_DCA'][$strTable]['list']['operations']);

        if (!empty($skipOperations) && !isset($options['operations'])) {
            $operations = array_diff($operations, $skipOperations);
        }

        foreach ($GLOBALS['TL_DCA'][$strTable]['list']['operations'] as $k => $v) {
            if (!\in_array($k, $operations)) {
                continue;
            }

            $v = \is_array($v) ? $v : [$v];
            $id = StringUtil::specialchars(rawurldecode($arrRow['id']));

            $label = $v['label'][0] ?: $k;
            $title = sprintf($v['label'][1] ?: $k, $id);
            $attributes = ('' != $v['attributes']) ? ' '.ltrim(sprintf($v['attributes'], $id, $id)) : '';

            // Add the key as CSS class
            if (false !== strpos($attributes, 'class="')) {
                $attributes = str_replace('class="', 'class="'.$k.' ', $attributes);
            } else {
                $attributes = ' class="'.$k.'"'.$attributes;
            }

            // Call a custom function instead of using the default button
            if (\is_array($v['button_callback'])) {
                $callback = System::importStatic($v['button_callback'][0]);
                $return .= $callback->{$v['button_callback'][1]}($arrRow, $v['href'], $label, $title, $v['icon'], $attributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, null, null, $this);

                continue;
            } elseif (\is_callable($v['button_callback'])) {
                $return .= $v['button_callback']($arrRow, $v['href'], $label, $title, $v['icon'], $attributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, null, null, $this);

                continue;
            }

            // Generate all buttons except "move up" and "move down" buttons
            if ('move' != $k && 'move' != $v) {
                if ('show' == $k) {
                    $return .= '<a href="'.Controller::addToUrl($v['href'].'&amp;id='.$arrRow['id'].'&amp;popup=1&amp;rt='.\RequestToken::get()).'" title="'.StringUtil::specialchars($title).'" onclick="Backend.openModalIframe({\'title\':\''.StringUtil::specialchars(str_replace("'", "\\'",
                            sprintf($GLOBALS['TL_LANG'][$strTable]['show'][1], $arrRow['id']))).'\',\'url\':this.href});return false"'.$attributes.'>'.Image::getHtml($v['icon'], $label).'</a> ';
                } else {
                    $return .= '<a href="'.Controller::addToUrl($v['href'].'&amp;id='.$arrRow['id'].(\Input::get('nb') ? '&amp;nc=1' : '')).'&amp;rt='.RequestToken::get().'" title="'.\StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($v['icon'], $label).'</a> ';
                }

                continue;
            }
        }

        return trim($return);
    }

    public function generateSitemap()
    {
        $automator = System::importStatic('Automator');
        $automator->generateSitemap();
    }

    /**
     * Mostly used for Form::prepareSpecialValueForOutput().
     *
     * @param $activeRecord
     */
    public function getDCTable(string $table, $activeRecord): DC_Table_Utils
    {
        $dc = new DC_Table_Utils($table);
        $dc->activeRecord = $activeRecord;
        $dc->id = $activeRecord->id;

        return $dc;
    }

    public function getAuthorNameByUserId($id)
    {
        if (null !== ($user = $this->container->get('huh.utils.model')->findModelInstanceByPk('tl_user', $id))) {
            return $user->name;
        }

        return false;
    }

    public function getAuthorNameLinkByUserId($id)
    {
        if (null !== ($user = $this->container->get('huh.utils.model')->findModelInstanceByPk('tl_user', $id))) {
            return '<strong>'.Controller::replaceInsertTags('{{email_open::'.$user->email.'}}').$user->name.'</a></strong>';
        }

        return false;
    }

    public function setFieldsToReadOnly(&$dca, array $config = [])
    {
        $skipFields = $config['skipFields'] ?? [];

        foreach ($dca['fields'] as $field => &$data) {
            if (\in_array($field, $skipFields)) {
                continue;
            }

            switch ($data['inputType']) {
                case 'checkbox':
                case 'radio':
                case 'radioTable':
                    $data['eval']['disabled'] = true;

                    break;

                case 'select':
                case 'imageSize':
                    $data['eval']['readonly'] = true;
                    $data['eval']['class'] = 'readonly';

                    break;

                case 'fileTree':
                case 'metaWizard':
                case 'tagsinput':
                    $data['eval']['readonly'] = true;
                    $data['eval']['tl_class'] = $data['eval']['tl_class'].' readonly';

                    break;

                case 'multiColumnEditor':
                    $data['eval']['readonly'] = true;

                    $this->setFieldsToReadOnly($data['eval']['multiColumnEditor'], $config);

                    break;

                default:
                    $data['eval']['readonly'] = true;

                    // TODO dispatch event for custom
                    break;
            }
        }
    }

    public function getTranslatedModuleNameByTable(string $table)
    {
        foreach ($GLOBALS['BE_MOD'] as $groupName => $groupModules) {
            if (empty($groupModules)) {
                continue;
            }

            foreach ($groupModules as $moduleName => $moduleConfig) {
                if (!isset($moduleConfig['tables']) || !\is_array($moduleConfig['tables'])) {
                    continue;
                }

                if (\in_array($table, $moduleConfig['tables'])) {
                    return StringUtil::specialchars($GLOBALS['TL_LANG']['MOD'][$moduleName][0]);
                }
            }
        }

        return false;
    }

    public function getRenderedDiff(string $table, array $source, array $target, array $config = [])
    {
        $result = '';

        $skipFields = $config['skipFields'] ?? [];
        $restrictFields = $config['restrictFields'] ?? [];
        $tableCallbacks = $config['tableCallbacks'] ?? [];

        $this->loadDc($table);
        $this->loadLanguageFile($table);

        $dca = $GLOBALS['TL_DCA'][$table];

        $arrayUtil = System::getContainer()->get('huh.utils.array');

        // Get the order fields
        $dcaExtractor = DcaExtractor::getInstance($table);
        $fields = $dcaExtractor->getFields();
        $orderFields = $dcaExtractor->getOrderFields();

        // Find the changed fields and highlight the changes
        foreach ($target as $k => $v) {
            if (empty($restrictFields) && \in_array($k, $skipFields)) {
                continue;
            }

            if (!empty($restrictFields) && !\in_array($k, $restrictFields)) {
                continue;
            }

            if ($source[$k] != $target[$k]) {
                if ($dca['fields'][$k]['eval']['doNotShow'] || $dca['fields'][$k]['eval']['hideInput']) {
                    continue;
                }

                $isBinary = 0 === strncmp($fields[$k], 'binary(', 7) || 0 === strncmp($fields[$k], 'blob ', 5);

                if ($dca['fields'][$k]['eval']['multiple'] || \in_array($k, $orderFields)) {
                    if (isset($dca['fields'][$k]['eval']['csv'])) {
                        $delimiter = $dca['fields'][$k]['eval']['csv'];

                        if (isset($target[$k])) {
                            $target[$k] = preg_replace('/'.preg_quote($delimiter, ' ?/').'/', $delimiter.' ', $target[$k]);
                        }

                        if (isset($source[$k])) {
                            $source[$k] = preg_replace('/'.preg_quote($delimiter, ' ?/').'/', $delimiter.' ', $source[$k]);
                        }
                    } else {
                        // Convert serialized arrays into strings
                        if (\is_array(($tmp = StringUtil::deserialize($target[$k]))) && !\is_array($target[$k])) {
                            $target[$k] = $arrayUtil->implodeRecursive($tmp, $isBinary);
                        }

                        if (\is_array(($tmp = StringUtil::deserialize($source[$k]))) && !\is_array($source[$k])) {
                            $source[$k] = $arrayUtil->implodeRecursive($tmp, $isBinary);
                        }
                    }
                }

                unset($tmp);

                // Convert binary UUIDs to their hex equivalents (see #6365)
                if ($isBinary) {
                    if (Validator::isBinaryUuid($target[$k])) {
                        $target[$k] = StringUtil::binToUuid($target[$k]);
                    }

                    if (Validator::isBinaryUuid($source[$k])) {
                        $source[$k] = StringUtil::binToUuid($source[$k]);
                    }
                }

                // Convert date fields
                if ('date' == $dca['fields'][$k]['eval']['rgxp']) {
                    $target[$k] = \Date::parse(Config::get('dateFormat'), $target[$k] ?: '');
                    $source[$k] = \Date::parse(Config::get('dateFormat'), $source[$k] ?: '');
                } elseif ('time' == $dca['fields'][$k]['eval']['rgxp']) {
                    $target[$k] = \Date::parse(Config::get('timeFormat'), $target[$k] ?: '');
                    $source[$k] = \Date::parse(Config::get('timeFormat'), $source[$k] ?: '');
                } elseif ('datim' == $dca['fields'][$k]['eval']['rgxp'] || 'tstamp' == $k) {
                    $target[$k] = \Date::parse(Config::get('datimFormat'), $target[$k] ?: '');
                    $source[$k] = \Date::parse(Config::get('datimFormat'), $source[$k] ?: '');
                }

                // Decode entities if the "decodeEntities" flag is not set (see #360)
                if (empty($dca['fields'][$k]['eval']['decodeEntities'])) {
                    $target[$k] = StringUtil::decodeEntities($target[$k]);
                    $source[$k] = StringUtil::decodeEntities($source[$k]);
                }

                // Convert strings into arrays
                if (!\is_array($target[$k])) {
                    $target[$k] = explode("\n", $target[$k]);
                }

                if (!\is_array($source[$k])) {
                    $source[$k] = explode("\n", $source[$k]);
                }

                // custom callbacks to modify data
                if (isset($tableCallbacks[$table]) && \is_callable($tableCallbacks[$table])) {
                    $tableCallbacks[$table]($k, $v, $source, $target);
                }

                $diff = new \Diff($source[$k], $target[$k]);
                $result .= $diff->render(new DiffRenderer(['field' => ($dca['fields'][$k]['label'][0] ?: (isset($GLOBALS['TL_LANG']['MSC'][$k]) ? (\is_array($GLOBALS['TL_LANG']['MSC'][$k]) ? $GLOBALS['TL_LANG']['MSC'][$k][0] : $GLOBALS['TL_LANG']['MSC'][$k]) : $k))]));
            }
        }

        // Identical versions
        if ('' == $result) {
            $result = '<p>'.$GLOBALS['TL_LANG']['MSC']['identicalVersions'].'</p>';
        }

        return $result;
    }

    public function prepareRowEntryForList($table, string $field, $value)
    {
        $this->loadDc($table);
        $this->loadLanguageFile($table);

        $dca = $GLOBALS['TL_DCA'][$table];

        $arrayUtil = System::getContainer()->get('huh.utils.array');

        // Get the order fields
        $dcaExtractor = DcaExtractor::getInstance($table);
        $fields = $dcaExtractor->getFields();
        $orderFields = $dcaExtractor->getOrderFields();

        if ($dca['fields'][$field]['eval']['doNotShow'] || $dca['fields'][$field]['eval']['hideInput']) {
            return '';
        }

        $sql = \is_array($fields[$field]) ? $fields[$field]['type'] : $fields[$field];

        $isBinary = 0 === strncmp($sql, 'binary(', 7) || 0 === strncmp($sql, 'blob ', 5);

        if ($dca['fields'][$field]['eval']['multiple'] || \in_array($field, $orderFields)) {
            if (isset($dca['fields'][$field]['eval']['csv'])) {
                $delimiter = $dca['fields'][$field]['eval']['csv'];

                if ($value) {
                    $value = preg_replace('/'.preg_quote($delimiter, ' ?/').'/', $delimiter.' ', $value);
                }
            } else {
                // Convert serialized arrays into strings
                if (\is_array(($tmp = StringUtil::deserialize($value))) && !\is_array($value)) {
                    $value = $arrayUtil->implodeRecursive($tmp, $isBinary);
                }
            }
        }

        unset($tmp);

        // Convert binary UUIDs to their hex equivalents (see #6365)
        if ($isBinary) {
            if (Validator::isBinaryUuid($value)) {
                $value = StringUtil::binToUuid($value);
            }
        }

        // Convert date fields
        if ('date' == $dca['fields'][$field]['eval']['rgxp']) {
            $value = \Date::parse(Config::get('dateFormat'), $value ?: '');
        } elseif ('time' == $dca['fields'][$field]['eval']['rgxp']) {
            $value = \Date::parse(Config::get('timeFormat'), $value ?: '');
        } elseif ('datim' == $dca['fields'][$field]['eval']['rgxp'] || 'tstamp' == $field) {
            $value = \Date::parse(Config::get('datimFormat'), $value ?: '');
        }

        // Decode entities if the "decodeEntities" flag is not set (see #360)
        if (empty($dca['fields'][$field]['eval']['decodeEntities'])) {
            $value = StringUtil::decodeEntities($value);
        }

        return $value;
    }

    public function getFieldLabel(string $table, string $field)
    {
        $this->loadDc($table);
        $this->loadLanguageFile($table);

        $dca = $GLOBALS['TL_DCA'][$table];

        return $dca['fields'][$field]['label'][0] ?: (isset($GLOBALS['TL_LANG']['MSC'][$field]) ? (\is_array($GLOBALS['TL_LANG']['MSC'][$field]) ? $GLOBALS['TL_LANG']['MSC'][$field][0] : $GLOBALS['TL_LANG']['MSC'][$field]) : $field);
    }
}

<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Dca;

use Contao\BackendUser;
use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\DataContainer;
use Contao\DcaExtractor;
use Contao\DiffRenderer;
use Contao\FrontendUser;
use Contao\Image;
use Contao\Input;
use Contao\Model;
use Contao\RequestToken;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use Doctrine\DBAL\Connection;
use HeimrichHannot\UtilsBundle\Choice\ModelInstanceChoice;
use HeimrichHannot\UtilsBundle\Driver\DC_Table_Utils;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Routing\RoutingUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DcaUtil
{
    const PROPERTY_SESSION_ID = 'sessionID';
    const PROPERTY_AUTHOR = 'author';
    const PROPERTY_AUTHOR_TYPE = 'authorType';

    const AUTHOR_TYPE_NONE = 'none';
    const AUTHOR_TYPE_MEMBER = 'member';
    const AUTHOR_TYPE_USER = 'user';
    const AUTHOR_TYPE_SESSION = 'session';

    /** @var ContaoFrameworkInterface */
    protected $framework;
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var RoutingUtil
     */
    private $routingUtil;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(ContainerInterface $container, ContaoFrameworkInterface $framework, RoutingUtil $routingUtil, Connection $connection)
    {
        $this->container = $container;
        $this->framework = $framework;
        $this->routingUtil = $routingUtil;
        $this->connection = $connection;
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
     * Options:
     * - attributes: (array) Link attributes as key value pairs. Will override title and style option. href and onclick are not allowed and will be removed from list.
     * - title: (string) Overrride default link title
     * - style: (string) Override default css style properties
     * - onclick: (string) Override default onclick javascript code
     * - icon: (string) Link icon to show as link text. Overrides default icon.
     * - linkText: (string) A linkTitle to show as link text. Will be displayed after the link icon. Default empty.
     * - url-only: (boolean) Return only url instead of a complete link element
     *
     * @param array $parameter An array of parameter. Using string is deprecated and will be removed in a future version.
     *
     * @return string
     */
    public function getPopupWizardLink($parameter, array $options = [])
    {
        if (\is_string($parameter)) {
            @trigger_error('Using string as parameter is deprecated and will be removed in a future version.', \E_USER_DEPRECATED);
            $result = [];
            $query = parse_url($parameter, \PHP_URL_QUERY);

            if (\is_string($query)) {
                $parameter = $query;
            }
            parse_str($parameter, $result);
            $parameter = $result;
        }

        $route = $options['route'] ?? 'contao_backend';

        $parameter['popup'] = 1;
        $parameter['nb'] = 1;

        $url = $this->routingUtil->generateBackendRoute($parameter, true, true, $route);

        if (isset($options['url-only']) && true === $options['url-only']) {
            return $url;
        }

        $attributes = [];

        if (isset($options['attributes'])) {
            $attributes = $options['attributes'];
        }

        // title
        if (!isset($options['title']) || !$options['title']) {
            $title = $GLOBALS['TL_LANG']['tl_content']['edit'][0];
        } else {
            $title = StringUtil::specialchars($options['title']);
        }

        if (!isset($attributes['title'])) {
            $attributes['title'] = $title;
        }

        // style
        $style = !isset($options['style']) ? 'padding-left: 5px; padding-top: 2px; display: inline-block;' : $options['style'];

        if (!empty($style) && !isset($attributes['style'])) {
            $attributes['style'] = $style;
        }

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

        if (!isset($attributes['onclick'])) {
            $attributes['onclick'] = $onclick;
        }

        // link text and icon
        $linkText = '';

        if (!isset($options['icon'])) {
            $linkText .= $this->framework->getAdapter(Image::class)->getHtml('alias.svg', $title, 'style="vertical-align:top"');
        } elseif (!empty($options['icon'])) {
            $linkText = $this->framework->getAdapter(Image::class)->getHtml($options['icon'], $title, 'style="vertical-align:top"');
        }

        if (isset($options['linkText']) || !empty($options['linkText'])) {
            $linkText .= $options['linkText'];
        }

        // Attributes
        $attributeQuery = '';

        foreach ($attributes as $key => $value) {
            if (\in_array($key, ['href', 'onclick'])) {
                continue;
            }
            $attributeQuery .= $key.'="'.htmlspecialchars($value).'" ';
        }

        return sprintf(
            '<a href="%s" %s %s>%s</a>',
            $url,
            $attributeQuery,
            $onclick,
            $linkText
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
    public function setDefaultsFromDca($strTable, $varData = null, bool $includeSql = false)
    {
        $this->framework->getAdapter(Controller::class)->loadDataContainer($strTable);

        if (empty($GLOBALS['TL_DCA'][$strTable])) {
            return $varData;
        }

        $dbFields = [];

        foreach (Database::getInstance()->listFields($strTable) as $data) {
            if (!isset($data['default'])) {
                continue;
            }

            $dbFields[$data['name']] = $data['default'];
        }

        // Get all default values for the new entry
        foreach ($GLOBALS['TL_DCA'][$strTable]['fields'] as $k => $v) {
            $addDefaultValue = false;
            $defaultValue = null;

            // check sql definition
            if ($includeSql && isset($dbFields[$k])) {
                $addDefaultValue = true;
                $defaultValue = $dbFields[$k];
            }

            // check dca default value
            if (\array_key_exists('default', $v)) {
                $addDefaultValue = true;
                $defaultValue = \is_array($v['default']) ? serialize($v['default']) : $v['default'];
            }

            if (!$addDefaultValue) {
                continue;
            }

            // Encrypt the default value (see #3740)
            if ($GLOBALS['TL_DCA'][$strTable]['fields'][$k]['eval']['encrypt']) {
                $defaultValue = $this->container->get('huh.utils.encryption')->encrypt($defaultValue);
            }

            if ($addDefaultValue) {
                if (\is_object($varData)) {
                    $varData->{$k} = $defaultValue;
                } else {
                    if (null === $varData) {
                        $varData = [];
                    }

                    if (\is_array($varData)) {
                        $varData[$k] = $defaultValue;
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

            try {
                return \call_user_func_array([$instance, $callback[1]], $arguments);
            } catch (\Error $e) {
                return null;
            }
        } elseif (\is_callable($array[$property.'_callback'])) {
            try {
                return \call_user_func_array($array[$property.'_callback'], $arguments);
            } catch (\Error $e) {
                return null;
            }
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
            $destinationDca['palettes']['__selector__'] = array_merge([$overrideFieldname], isset($destinationDca['palettes']['__selector__']) && \is_array($destinationDca['palettes']['__selector__']) ? $destinationDca['palettes']['__selector__'] : []);

            // copy field
            $destinationDca['fields'][$field] = $sourceDca['fields'][$field];

            // subpalette
            $destinationDca['subpalettes'][$overrideFieldname] = $field;

            if (!isset($options['skipLocalization']) || !$options['skipLocalization']) {
                $GLOBALS['TL_LANG'][$destinationTable][$overrideFieldname] = [
                    $this->container->get('translator')->trans('huh.utils.misc.override.label', [
                        '%fieldname%' => $GLOBALS['TL_DCA'][$sourceTable]['fields'][$field]['label'][0] ?? $field,
                    ]),
                    $this->container->get('translator')->trans('huh.utils.misc.override.desc', [
                        '%fieldname%' => $GLOBALS['TL_DCA'][$sourceTable]['fields'][$field]['label'][0] ?? $field,
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

        $pm = PaletteManipulator::create();

        $dca = &$GLOBALS['TL_DCA'][$table];
        $arrayUtil = $this->container->get('huh.utils.array');

        // Contao 4.4 fix
        $replaceFields = [];

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
                                    $pm->addField($subPaletteField, $field);
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
                            $pm->addField($subPaletteField, $field);
                        }

                        // remove nested field in order to avoid its normal "selector" behavior
                        $arrayUtil->removeValue($field, $dca['palettes']['__selector__']);
                        unset($dca['subpalettes'][$field]);
                    }
                }
            }

            $replaceFields[] = $field;

//            $pm->addField('override'.ucfirst($field), $field)->removeField($field);
        }

        $pm->applyToPalette('default', $table);

        foreach ($replaceFields as $replaceField) {
            $dca['palettes']['default'] = str_replace($replaceField, 'override'.ucfirst($replaceField), $dca['palettes']['default']);
        }
    }

    /**
     * Return if the current alias already exist in table.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function aliasExist(string $alias, int $id, string $table, $options = []): bool
    {
        $aliasField = $options['aliasField'] ?? 'alias';

        $stmt = $this->connection->prepare('SELECT id FROM '.$table.' WHERE '.$aliasField.'=? AND id!=?');
        $stmt->execute([$alias, $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Generate an alias with unique check.
     *
     * @param mixed       $alias       The current alias (if available)
     * @param int         $id          The entity's id
     * @param string|null $table       The entity's table (pass a comma separated list if the validation should be expanded to multiple tables like tl_news AND tl_member. ATTENTION: the first table needs to be the one we're currently in). Pass null to skip unqiue check.
     * @param string      $title       The value to use as a base for the alias
     * @param bool        $keepUmlauts Set to true if German umlauts should be kept
     *
     * @throws \Exception
     *
     * @return string
     */
    public function generateAlias(?string $alias, int $id, ?string $table, string $title, bool $keepUmlauts = true, $options = [])
    {
        $autoAlias = false;
        $aliasField = $options['aliasField'] ?? 'alias';

        // Generate alias if there is none
        if (empty($alias)) {
            $autoAlias = true;
            $alias = StringUtil::generateAlias($title);
        }

        if (!$keepUmlauts) {
            $alias = preg_replace(['/ä/i', '/ö/i', '/ü/i', '/ß/i'], ['ae', 'oe', 'ue', 'ss'], $alias);
        }

        if (null === $table) {
            return $alias;
        }

        $originalAlias = $alias;

        // multiple tables?
        if (false !== strpos($table, ',')) {
            $tables = explode(',', $table);

            foreach ($tables as $i => $partTable) {
                // the table in which the entity is
                if (0 === $i) {
                    if ($this->aliasExist($alias, $id, $table, $options)) {
                        if (!$autoAlias) {
                            throw new \InvalidArgumentException(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $alias));
                        }

                        $alias = $originalAlias.'-'.$id;
                    }
                } else {
                    // another table
                    $stmt = $this->connection->prepare("SELECT id FROM {$partTable} WHERE ' . $aliasField . '=?");
                    $stmt->execute([$alias]);

                    // Check whether the alias exists
                    if ($stmt->rowCount() > 0) {
                        throw new \InvalidArgumentException(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $alias));
                    }
                }
            }
        } else {
            if (!$this->aliasExist($alias, $id, $table, $options)) {
                return $alias;
            }

            // Check whether the alias exists
            if (!$autoAlias) {
                throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $alias));
            }

            // Add ID to alias
            $alias .= '-'.$id;
        }

        return $alias;
    }

    public function addAuthorFieldAndCallback(string $table, string $fieldPrefix = '')
    {
        $this->framework->getAdapter(Controller::class)->loadDataContainer($table);

        // callbacks
        $GLOBALS['TL_DCA'][$table]['config']['oncreate_callback']['setAuthorIDOnCreate'] = [self::class, 'setAuthorIDOnCreate'];
        $GLOBALS['TL_DCA'][$table]['config']['onload_callback']['modifyAuthorPaletteOnLoad'] = [self::class, 'modifyAuthorPaletteOnLoad', true];

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
                // session is only added if it's already set in the dca
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
            'default' => '0',
            'options_callback' => function () {
                return $this->container->get('huh.utils.choice.model_instance')->getCachedChoices([
                    'dataContainer' => 'tl_member',
                    'labelPattern' => '%firstname% %lastname% (ID %id%)',
                ]);
            },
            'save_callback' => [function ($value, $dc) {
                if (!$value) {
                    return 0;
                }

                return $value;
            }],
            'eval' => [
                'doNotCopy' => true,
                'chosen' => true,
                'includeBlankOption' => true,
                'tl_class' => 'w50',
            ],
            'sql' => "varchar(64) NOT NULL default '0'",
        ];
    }

    public function setAuthorIDOnCreate(string $table, int $id, array $row, DataContainer $dc)
    {
        $model = $this->container->get(ModelUtil::class)->findModelInstanceByPk($table, $id);
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
            } else {
                // php session
                $model->{static::PROPERTY_AUTHOR_TYPE} = static::AUTHOR_TYPE_SESSION;
                $model->{static::PROPERTY_AUTHOR} = session_id();
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
            unset($dca['fields'][static::PROPERTY_AUTHOR]);
        }

        if ($model->{static::PROPERTY_AUTHOR_TYPE} == static::AUTHOR_TYPE_USER) {
            $dca['fields'][static::PROPERTY_AUTHOR]['options_callback'] = function () {
                return $this->container->get(ModelInstanceChoice::class)->getCachedChoices([
                    'dataContainer' => 'tl_user',
                    'labelPattern' => '%name% (ID %id%)',
                ]);
            };
        }

        if ($model->{static::PROPERTY_AUTHOR_TYPE} == static::AUTHOR_TYPE_SESSION) {
            $dca['fields'][static::PROPERTY_AUTHOR_TYPE]['options'] = array_merge($dca['fields'][static::PROPERTY_AUTHOR_TYPE]['options'], [static::AUTHOR_TYPE_SESSION]);
            // do not allow to edit in backend
            $dca['fields'][static::PROPERTY_AUTHOR_TYPE]['eval']['readonly'] = true;

            unset($dca['fields'][static::PROPERTY_AUTHOR]['options_callback']);
            $dca['fields'][static::PROPERTY_AUTHOR]['inputType'] = 'text';
            // do not allow to edit in backend
            $dca['fields'][static::PROPERTY_AUTHOR]['eval']['readonly'] = true;
            $dca['fields'][static::PROPERTY_AUTHOR]['label'][0] = $GLOBALS['TL_LANG']['MSC']['utilsBundle'][static::PROPERTY_AUTHOR_TYPE][self::AUTHOR_TYPE_SESSION];
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
        $fields = $config['fields'] ?? [];

        foreach ($dca['fields'] as $field => &$data) {
            if (!empty($fields)) {
                if (!\in_array($field, $fields)) {
                    continue;
                }
            } elseif (\in_array($field, $skipFields)) {
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

    /**
     * Returns the set of pid and sorting to be used in an sql update statement. Also updates the existing records according to the usage.
     *
     * The method can be used in several ways:
     *
     * <ul>
     *   <li>Insert in an archive of a certain pid as first item: $pid must be set (0 is also ok), $insertAfterId needs to be null</li>
     *   <li>Insert after a record of a certain id: $insertAfterId must be set, $pid can be set if necessary</li>
     * </ul>
     *
     * @example
     *
     * // insert a new record after another one with the ID 82
     *
     * $news = new \Contao\NewsModel();
     * $news->pid = 3;
     * $news->tstamp = time();
     * $news->title = 'Something';
     * $news->save();
     * $set = System::getContainer()->get('huh.utils.dca')->getNewSortingPosition(
     *   'tl_news', $news->id, 3, 82
     * );
     *
     * // store the returned set to the news record created above as usual
     *
     * Hint: Mostly taken from DC_Table::getNewPosition(). Removed: handling if only a pid field is present, mode handling (since we don't have it in this context).
     */
    public function getNewSortingPosition(string $table, int $id, $pid = null, $insertAfterId = null): array
    {
        $set = [];

        /* @var Database $db */
        if (!($db = $this->framework->createInstance(Database::class))) {
            return $set;
        }

        // If there is pid and sorting
        if ($db->fieldExists('pid', $table) && $db->fieldExists('sorting', $table)) {
            // PID is set (insert after or into the parent record)
            if (is_numeric($pid)) {
                // ID is set (insert after the current record)
                if ($insertAfterId) {
                    $objCurrentRecord = $db->prepare("SELECT * FROM $table WHERE id=? AND pid=?")
                        ->limit(1)
                        ->execute($insertAfterId, $pid);

                    // Select current record
                    if ($objCurrentRecord->numRows) {
                        $newSorting = null;
                        $curSorting = $objCurrentRecord->sorting;

                        $objNextSorting = $db->prepare("SELECT MIN(sorting) AS sorting FROM $table WHERE sorting>? AND pid=?")
                            ->execute($curSorting, $pid);

                        // Select sorting value of the next record
                        if ($objNextSorting->numRows && null !== $objNextSorting->sorting) {
                            $nxtSorting = $objNextSorting->sorting;

                            // Resort if the new sorting value is no integer or bigger than a MySQL integer field
                            if (0 != (($curSorting + $nxtSorting) % 2) || $nxtSorting >= 4294967295) {
                                $count = 1;

                                $objNewSorting = $db->prepare("SELECT id, sorting FROM $table WHERE pid=? AND id!=? ORDER BY sorting")->execute($pid, $id);

                                while ($objNewSorting->next()) {
                                    $db->prepare("UPDATE $table SET sorting=? WHERE id=? AND pid=?")
                                        ->execute(($count++ * 128), $objNewSorting->id, $pid);

                                    if ($objNewSorting->sorting == $curSorting) {
                                        $newSorting = ($count++ * 128);
                                    }
                                }
                            } // Else new sorting = (current sorting + next sorting) / 2
                            else {
                                $newSorting = (($curSorting + $nxtSorting) / 2);
                            }
                        } // Else new sorting = (current sorting + 128)
                        else {
                            $newSorting = ($curSorting + 128);
                        }

                        // Set new sorting
                        $set['sorting'] = (int) $newSorting;

                        return $set;
                    }
                } else {
                    // insert in first place
                    $newPID = null;
                    $newSorting = null;

                    $newPID = $pid;

                    $minSorting = $db->prepare("SELECT MIN(sorting) AS sorting FROM $table WHERE pid=?")->execute($pid);

                    // Select sorting value of the first record
                    if ($minSorting->numRows) {
                        $curSorting = $minSorting->sorting;

                        // Resort if the new sorting value is not an integer or smaller than 1
                        if (0 != ($curSorting % 2) || $curSorting < 1) {
                            $objNewSorting = $db->prepare("SELECT id FROM $table WHERE pid=? ORDER BY sorting")->execute($pid);

                            $count = 2;
                            $newSorting = 128;

                            while ($objNewSorting->next()) {
                                $db->prepare("UPDATE $table SET sorting=? WHERE id=?")
                                    ->limit(1)
                                    ->execute(($count++ * 128), $objNewSorting->id);
                            }
                        } // Else new sorting = (current sorting / 2)
                        else {
                            $newSorting = ($curSorting / 2);
                        }
                    } // Else new sorting = 128
                    else {
                        $newSorting = 128;
                    }

                    // Set new sorting and new parent ID
                    $set['pid'] = (int) $newPID;
                    $set['sorting'] = (int) $newSorting;
                }
            }
        } // If there is only sorting
        elseif ($db->fieldExists('sorting', $table)) {
            // ID is set (insert after the current record)
            if ($insertAfterId) {
                $objCurrentRecord = $db->prepare("SELECT * FROM $table WHERE id=?")
                    ->limit(1)
                    ->execute($insertAfterId);

                // Select current record
                if ($objCurrentRecord->numRows) {
                    $newSorting = null;
                    $curSorting = $objCurrentRecord->sorting;

                    $objNextSorting = $db->prepare("SELECT MIN(sorting) AS sorting FROM $table WHERE sorting>?")
                        ->execute($curSorting);

                    // Select sorting value of the next record
                    if ($objNextSorting->numRows) {
                        $nxtSorting = $objNextSorting->sorting;

                        // Resort if the new sorting value is no integer or bigger than a MySQL integer field
                        if (0 != (($curSorting + $nxtSorting) % 2) || $nxtSorting >= 4294967295) {
                            $count = 1;

                            $objNewSorting = $db->execute("SELECT id, sorting FROM $table ORDER BY sorting");

                            while ($objNewSorting->next()) {
                                $db->prepare("UPDATE $table SET sorting=? WHERE id=?")
                                    ->execute(($count++ * 128), $objNewSorting->id);

                                if ($objNewSorting->sorting == $curSorting) {
                                    $newSorting = ($count++ * 128);
                                }
                            }
                        } // Else new sorting = (current sorting + next sorting) / 2
                        else {
                            $newSorting = (($curSorting + $nxtSorting) / 2);
                        }
                    } // Else new sorting = (current sorting + 128)
                    else {
                        $newSorting = ($curSorting + 128);
                    }

                    // Set new sorting
                    $set['sorting'] = (int) $newSorting;

                    return $set;
                }
            }

            // ID is not set or not found (insert at the end)
            $objNextSorting = $db->execute('SELECT MAX(sorting) AS sorting FROM '.$table);
            $set['sorting'] = ((int) $objNextSorting->sorting + 128);
        }

        return $set;
    }

    /**
     * Taken from \Contao\DataContainer.
     */
    public function getCurrentPaletteName(string $table, int $id): ?string
    {
        // Check whether there are selector fields
        if (!empty($GLOBALS['TL_DCA'][$table]['palettes']['__selector__'])) {
            $sValues = [];
            $subpalettes = [];

            $objFields = Database::getInstance()->prepare('SELECT * FROM '.$table.' WHERE id=?')
                ->limit(1)
                ->execute($id);

            // Get selector values from DB
            if ($objFields->numRows > 0) {
                foreach ($GLOBALS['TL_DCA'][$table]['palettes']['__selector__'] as $name) {
                    $trigger = $objFields->$name;

                    // Overwrite the trigger
                    if (Input::post('FORM_SUBMIT') == $table) {
                        $key = ('editAll' == Input::get('act')) ? $name.'_'.$id : $name;

                        if (isset($_POST[$key])) {
                            $trigger = Input::post($key);
                        }
                    }

                    if ($trigger) {
                        if ('checkbox' == ($GLOBALS['TL_DCA'][$table]['fields'][$name]['inputType'] ?? null) && !($GLOBALS['TL_DCA'][$table]['fields'][$name]['eval']['multiple'] ?? null)) {
                            $sValues[] = $name;

                            // Look for a subpalette
                            if (isset($GLOBALS['TL_DCA'][$table]['subpalettes'][$name])) {
                                $subpalettes[$name] = $GLOBALS['TL_DCA'][$table]['subpalettes'][$name];
                            }
                        } else {
                            $sValues[] = $trigger;
                            $key = $name.'_'.$trigger;

                            // Look for a subpalette
                            if (isset($GLOBALS['TL_DCA'][$table]['subpalettes'][$key])) {
                                $subpalettes[$name] = $GLOBALS['TL_DCA'][$table]['subpalettes'][$key];
                            }
                        }
                    }
                }
            }

            // Build possible palette names from the selector values
            if (empty($sValues)) {
                $names = ['default'];
            } elseif (\count($sValues) > 1) {
                foreach ($sValues as $k => $v) {
                    // Unset selectors that just trigger subpalettes (see #3738)
                    if (isset($GLOBALS['TL_DCA'][$table]['subpalettes'][$v])) {
                        unset($sValues[$k]);
                    }
                }

                $names = $this->combiner($sValues);
            } else {
                $names = [$sValues[0]];
            }

            // Get an existing palette
            foreach ($names as $paletteName) {
                if (isset($GLOBALS['TL_DCA'][$table]['palettes'][$paletteName])) {
                    return $paletteName;
                }
            }
        }

        return null;
    }

    /**
     * Returns true if the field is in at least one sub palette.
     */
    public function isSubPaletteField(string $field, string $table): bool
    {
        $this->framework->getAdapter(Controller::class)->loadDataContainer($table);

        if (!isset($GLOBALS['TL_DCA'][$table]['subpalettes']) || !\is_array($GLOBALS['TL_DCA'][$table]['subpalettes'])) {
            return false;
        }

        foreach ($GLOBALS['TL_DCA'][$table]['subpalettes'] as $fields) {
            if (\in_array($field, explode(',', $fields))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the selector of the sub palette a field is placed in. Currently doesn't support fields in multiple sub palettes.
     */
    public function getSubPaletteFieldSelector(string $field, string $table): string
    {
        $this->framework->getAdapter(Controller::class)->loadDataContainer($table);

        if (!isset($GLOBALS['TL_DCA'][$table]['subpalettes']) || !\is_array($GLOBALS['TL_DCA'][$table]['subpalettes'])) {
            return false;
        }

        foreach ($GLOBALS['TL_DCA'][$table]['subpalettes'] as $name => $fields) {
            if (\in_array($field, explode(',', $fields))) {
                return $name;
            }
        }

        return false;
    }

    /**
     * Taken from \Contao\DataContainer.
     */
    private function combiner($names)
    {
        $return = [''];
        $names = array_values($names);

        for ($i = 0, $c = \count($names); $i < $c; ++$i) {
            $buffer = [];

            foreach ($return as $k => $v) {
                $buffer[] = (0 == $k % 2) ? $v : $v.$names[$i];
                $buffer[] = (0 == $k % 2) ? $v.$names[$i] : $v;
            }

            $return = $buffer;
        }

        return array_filter($return);
    }
}

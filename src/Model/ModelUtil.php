<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Model;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\DataContainer;
use Contao\Date;
use Contao\Model;
use Contao\Model\Collection;
use Contao\ModuleModel;
use Contao\PageModel;
use HeimrichHannot\UtilsBundle\Driver\DC_Table_Utils;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ModelUtil
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->framework = $container->get('contao.framework');
        $this->container = $container;
    }

    /**
     * Set the entity defaults from dca config (for new model entry).
     *
     * @return Model The modified model, containing the default values from all dca fields
     */
    public function setDefaultsFromDca(Model $objModel)
    {
        return $this->container->get('huh.utils.dca')->setDefaultsFromDca($objModel->getTable(), $objModel);
    }

    /**
     * Returns a model instance if for a given table and id(primary key).
     * Return null, if model type or model instance with given id not exist.
     *
     * @param mixed $pk
     *
     * @return mixed
     */
    public function findModelInstanceByPk(string $table, $pk, array $options = [])
    {
        /* @var Model $adapter */
        if (!($modelClass = $this->framework->getAdapter(Model::class)->getClassFromTable($table))) {
            return null;
        }

        if (null === ($adapter = $this->framework->getAdapter($modelClass))) {
            return null;
        }

        return $adapter->findByPk($pk, $options);
    }

    /**
     * Returns model instances by given table and search criteria.
     *
     * @param mixed $columns
     * @param mixed $values
     *
     * @return mixed
     */
    public function findModelInstancesBy(string $table, $columns, $values, array $options = [])
    {
        /* @var Model $adapter */
        if (!($modelClass = $this->framework->getAdapter(Model::class)->getClassFromTable($table))) {
            return null;
        }

        if (null === ($adapter = $this->framework->getAdapter($modelClass))) {
            return null;
        }

        $this->fixTablePrefixForDcMultilingual($table, $columns, $options);

        if (\is_array($values) && (!isset($options['skipReplaceInsertTags']) || !$options['skipReplaceInsertTags'])) {
            $values = array_map('\Contao\Controller::replaceInsertTags', $values);
        }

        if (empty($columns)) {
            $columns = null;
        }

        return $adapter->findBy($columns, $values, $options);
    }

    /**
     * Return a single model instance by table and search criteria.
     *
     * @return mixed
     */
    public function findOneModelInstanceBy(string $table, array $columns, array $values, array $options = [])
    {
        /* @var Model $adapter */
        if (!($modelClass = $this->framework->getAdapter(Model::class)->getClassFromTable($table))) {
            return null;
        }

        if (null === ($adapter = $this->framework->getAdapter($modelClass))) {
            return null;
        }

        $this->fixTablePrefixForDcMultilingual($table, $columns, $options);

        if (\is_array($values) && (!isset($options['skipReplaceInsertTags']) || !$options['skipReplaceInsertTags'])) {
            $values = array_map('\Contao\Controller::replaceInsertTags', $values);
        }

        if (empty($columns)) {
            $columns = null;
        }

        return $adapter->findOneBy($columns, $values, $options);
    }

    /**
     * Returns multiple model instances by given table and ids.
     *
     * @return mixed
     */
    public function findMultipleModelInstancesByIds(string $table, array $ids, array $options = [])
    {
        /* @var Model $adapter */
        if (!($modelClass = $this->framework->getAdapter(Model::class)->getClassFromTable($table))) {
            return null;
        }

        if (null === ($adapter = $this->framework->getAdapter($modelClass))) {
            return null;
        }

        if ($this->container->get('huh.utils.dca')->isDcMultilingual($table) && $this->container->get('huh.utils.dca')->isDcMultilingual3()) {
            $table = 't1';
        }

        return $adapter->findBy(["$table.id IN(".implode(',', array_map('\intval', $ids)).')'], null, $options);
    }

    /**
     * Returns multiple model instances by given table and id or alias.
     *
     * @param mixed $idOrAlias
     *
     * @return mixed
     */
    public function findModelInstanceByIdOrAlias(string $table, $idOrAlias, array $options = [])
    {
        if (!($modelClass = $this->framework->getAdapter(Model::class)->getClassFromTable($table))) {
            return null;
        }

        /* @var Model $adapter */
        if (null === ($adapter = $this->framework->getAdapter($modelClass))) {
            return null;
        }

        if ($this->container->get('huh.utils.dca')->isDcMultilingual($table) && $this->container->get('huh.utils.dca')->isDcMultilingual3()) {
            $table = 't1';
        }

        $options = array_merge(
            [
                'limit' => 1,
                'column' => !is_numeric($idOrAlias) ? ["$table.alias=?"] : ["$table.id=?"],
                'value' => $idOrAlias,
                'return' => 'Model',
            ],
            $options
        );

        return $adapter->findByIdOrAlias($idOrAlias, $options);
    }

    public function callModelMethod(string $table, string $method, ...$args)
    {
        if (!($modelClass = $this->framework->getAdapter(Model::class)->getClassFromTable($table))) {
            return null;
        }

        if (null === ($adapter = $this->framework->getAdapter($modelClass))) {
            return null;
        }

        return \call_user_func_array([$adapter, $method], $args);
    }

    /**
     * Fixes existing table prefixed already aliased in MultilingualQueryBuilder::buildQueryBuilderForFind().
     *
     * @param $columns
     *
     * @return array|mixed
     */
    public function fixTablePrefixForDcMultilingual(string $table, &$columns, array &$options = [])
    {
        if (!$this->container->get('huh.utils.dca')->isDcMultilingual($table) || !$this->container->get('huh.utils.dca')->isDcMultilingual3()) {
            return $columns;
        }

        if (\is_array($columns)) {
            $fixed = [];

            foreach ($columns as $column) {
                $fixed[] = str_replace($table.'.', 't1.', $column);
            }

            $columns = $fixed;
        } else {
            $columns = str_replace($table.'.', 't1.', $columns);
        }

        if (\is_array($options) && isset($options['order'])) {
            $options['order'] = str_replace($table.'.', 't1.', $options['order']);
        }
    }

    public function getDcMultilingualTranslationRecord($table, $id, $language = null)
    {
        Controller::loadDataContainer($table);
        $dca = $GLOBALS['TL_DCA'][$table];

        $pidColumnName = $dca['config']['langPid'] ?: 'langPid';
        $langColumnName = $dca['config']['langColumnName'] ?: 'language';
        $language = $language ?: $this->getCurrentDcMultilingualLanguage($table, $id);

        if (!$language) {
            return false;
        }

        $record = $this->framework->createInstance(Database::class)->prepare("SELECT * FROM $table WHERE $pidColumnName=? AND $langColumnName=?")->limit(1)->execute($id, $language);

        if (null !== $record) {
            return $record->row();
        }
    }

    /**
     * Get the current dc_multilingual language even DC_Multilingual::edit() didn't run.
     * This can be used in onload_callbacks for example since here DC_Multilingual::edit() didn't run, yet.
     *
     * @return bool|mixed
     */
    public function getCurrentDcMultilingualLanguage(string $table, int $id)
    {
        $translatableLangs = $this->getDcMultilingualTranslatableLanguages($table);

        /** @var SessionInterface $objSessionBag */
        $objSessionBag = $this->container->get('session')->getBag('contao_backend');
        $sessionKey = 'dc_multilingual:'.$table.':'.$id;

        /** @var Request $request */
        $request = $this->container->get('request_stack')->getCurrentRequest();

        if ('tl_language' === $request->request->get('FORM_SUBMIT')) {
            $language = $request->request->get('language');
        } elseif ($objSessionBag->has($sessionKey)) {
            $language = $objSessionBag->get($sessionKey);
        }

        if (\in_array($language, $translatableLangs)) {
            return $language;
        }

        return false;
    }

    public function getDcMultilingualTranslatableLanguages(string $table)
    {
        Controller::loadDataContainer($table);
        $dca = $GLOBALS['TL_DCA'][$table];

        // Languages array
        if (isset($dca['config']['languages'])) {
            return $dca['config']['languages'];
        }

        return $this->getDcMultilingualRootPageLanguages();
    }

    /**
     * Get the list of languages based on root pages.
     *
     * @return array
     */
    public function getDcMultilingualRootPageLanguages()
    {
        $pages = $this->framework->createInstance(Database::class)->execute("SELECT DISTINCT language FROM tl_page WHERE type='root' AND language!=''");
        $languages = $pages->fetchEach('language');

        array_walk(
            $languages,
            function (&$value) {
                $value = str_replace('-', '_', $value);
            }
        );

        return $languages;
    }

    /**
     * Recursively finds the root parent.
     *
     * @return Model
     */
    public function findRootParentRecursively(string $parentProperty, string $table, Model $instance, bool $returnInstanceIfNoParent = true)
    {
        if (!$instance || !$instance->{$parentProperty}
            || null === ($parentInstance = $this->findModelInstanceByPk($table, $instance->{$parentProperty}))) {
            return $returnInstanceIfNoParent ? $instance : null;
        }

        return $this->findRootParentRecursively($parentProperty, $table, $parentInstance);
    }

    /**
     * Returns an array of a model instance's parents in ascending order, i.e. the root parent comes first.
     */
    public function findParentsRecursively(string $parentProperty, string $table, Model $instance): array
    {
        $parents = [];

        if (!$instance->{$parentProperty} || null === ($parentInstance = $this->findModelInstanceByPk($table, $instance->{$parentProperty}))) {
            return $parents;
        }

        return array_merge($this->findParentsRecursively($parentProperty, $table, $parentInstance), [$parentInstance]);
    }

    /**
     * Find all model instances for a given table.
     *
     * @param string $table      The table name
     * @param array  $arrOptions Additional query options
     */
    public function findAllModelInstances(string $table, array $arrOptions = []): ?Collection
    {
        if (!($modelClass = $this->framework->getAdapter(Model::class)->getClassFromTable($table))) {
            return null;
        }

        /* @var Model $adapter */
        if (null === ($adapter = $this->framework->getAdapter($modelClass))) {
            return null;
        }

        return $adapter->findAll($arrOptions);
    }

    /**
     * @param Model|object $instance
     *
     * @return mixed
     */
    public function computeStringPattern(string $pattern, $instance, string $table, array $specialValueConfig = [])
    {
        Controller::loadDataContainer($table);

        $dca = &$GLOBALS['TL_DCA'][$table];
        $dc = new DC_Table_Utils($table);
        $dc->id = $instance->id;
        $dc->activeRecord = $instance;

        return preg_replace_callback(
            '@%([^%]+)%@i',
            function ($matches) use ($instance, $dca, $dc, $specialValueConfig) {
                return $this->container->get('huh.utils.form')->prepareSpecialValueForOutput($matches[1], $instance->{$matches[1]}, $dc, $specialValueConfig);
            },
            $pattern
        );
    }

    /**
     * @param $instance
     * @param $table
     *
     * @return Model|mixed
     */
    public function getModelInstanceIfId($instance, $table)
    {
        if ($instance instanceof Model) {
            return $instance;
        }

        if ($instance instanceof Collection) {
            return $instance->current();
        }

        return $this->findModelInstanceByPk($table, $instance);
    }

    /**
     * Determine if given value is newer than DataContainer value.
     *
     * @param mixed $newValue
     */
    public function hasValueChanged($newValue, DataContainer $dc): bool
    {
        if (null !== ($entity = $this->findModelInstanceByPk($dc->table, $dc->id))) {
            return $newValue != $entity->{$dc->field};
        }

        return true;
    }

    /**
     * Get model instance value for given field.
     *
     * @return mixed|null
     */
    public function getModelInstanceFieldValue(string $field, string $table, int $id)
    {
        if (null !== ($entity = $this->findModelInstanceByPk($table, $id))) {
            return $entity->{$property};
        }

        return null;
    }

    /**
     * Find module pages.
     *
     * Returns page ids or models, where a frontend module is integrated
     *
     * Also search within blocks (heimrichhannot/contao-blocks)
     *
     * @param bool $collection Return PageModel Collection if true. Default: false
     * @param bool $useCache   If true, a filesystem cache will be used to save pages ids. Default: true
     *
     * @return array|Collection|PageModel|PageModel[]|null An array of page Ids (can be empty if no page found!), a PageModel collection or null
     */
    public function findModulePages(ModuleModel $module, $collection = false, $useCache = true)
    {
        $cache = new FilesystemCache();
        $modulePagesCache = $cache->get('huh.utils.model.modulepages');
        $pageIds = [];
        $cacheHit = false;

        if ($useCache && $cache->has('huh.utils.model.modulepages')) {
            $modulePagesCache = $cache->get('huh.utils.model.modulepages');

            if (\is_array($modulePagesCache) && \array_key_exists($module->id, $modulePagesCache)) {
                $pageIds = $modulePagesCache[$module->id];
                $cacheHit = true;
            }
        }

        if (!$cacheHit) {
            /** @var Database $db */
            $db = $this->framework->createInstance(Database::class);
            $result = $db->prepare("SELECT `tl_page`.`id` FROM `tl_page` JOIN `tl_article` ON `tl_article`.`pid` = `tl_page`.`id` JOIN `tl_content` ON `tl_content`.`pid` = `tl_article`.`id` WHERE `tl_content`.`type` = 'module' AND `tl_content`.`module` = ?")->execute($module->id);

            if ($result->count() > 0) {
                $pageIds = $result->fetchEach('id');
            }

            if (\array_key_exists('blocks', $this->container->getParameter('kernel.bundles'))) {
                $result = $db->prepare(
                    "SELECT `tl_page`.`id` FROM `tl_page`
                JOIN `tl_article` ON `tl_article`.`pid` = `tl_page`.`id`
                JOIN `tl_content` ON `tl_content`.`pid` = `tl_article`.`id`
                JOIN `tl_block` ON `tl_block`.`module` = `tl_content`.`module`
                JOIN `tl_block_module` ON `tl_block_module`.`pid` = `tl_block`.`id`
                WHERE `tl_block_module`.`type` = 'default' AND `tl_block_module`.`module` = ?"
                )->execute($module->id);

                if ($result->count() > 0) {
                    $pageIds = array_unique(array_merge($pageIds, $result->fetchEach('id')));
                }
            }
            $modulePagesCache[$module->id] = $pageIds;
            $cache->set('huh.utils.model.modulepages', $modulePagesCache);
        }

        if ($collection) {
            return $this->framework->getAdapter(PageModel::class)->findMultipleByIds($pageIds);
        }

        return $pageIds;
    }

    public function addPublishedCheckToModelArrays(string $table, string $publishedField, string $startField, string $stopField, array &$columns, array $options = [])
    {
        $t = $table;

        if (isset($options['ignoreFePreview']) || !BE_USER_LOGGED_IN) {
            $time = Date::floorToMinute();

            $invertPublishedField = isset($options['invertPublishedField']) && $options['invertPublishedField'];
            $invertStartStopFields = isset($options['invertStartStopFields']) && $options['invertStartStopFields'];

            $columns[] = "($t.$startField".($invertStartStopFields ? '!=' : '=')."'' OR $t.$startField".($invertStartStopFields ? '>' : '<=')."'$time') AND ($t.$stopField".($invertStartStopFields ? '!=' : '=')."'' OR $t.$stopField".($invertStartStopFields ? '<=' : '>')."'".($time + 60)."') AND $t.$publishedField".($invertPublishedField ? '!=' : '=')."'1'";
        }
    }
}

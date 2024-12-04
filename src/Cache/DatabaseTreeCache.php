<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Cache;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\System;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RequestStack;

class DatabaseTreeCache
{
    protected static array $cache = [];

    protected ContaoFramework $framework;

    /**
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @var Database
     */
    protected $database;

    /**
     * Tree cache directory.
     *
     * @var string
     */
    protected string $cacheDir;
    /**
     * @var RequestStack
     */
    protected RequestStack $requestStack;
    private ParameterBagInterface $parameterBag;
    private Utils $utils;

    public function __construct(
        ContaoFramework       $framework,
        Filesystem            $filesystem,
        RequestStack          $requestStack,
        ParameterBagInterface $parameterBag,
        Utils                 $utils
    )
    {
        $this->framework = $framework;
        $this->filesystem = $filesystem;
        $this->database = $this->framework->createInstance(Database::class);
        $this->requestStack = $requestStack;
        $this->parameterBag = $parameterBag;
        $this->utils = $utils;

        $this->cacheDir = $this->parameterBag->get('kernel.cache_dir').'/tree_cache';
    }

    /**
     * Generate tree cache.
     */
    public function loadDataContainer($table): void
    {
        if (!($this->parameterBag->get('huh_utils')['cache']['enable_generate_database_tree_cache'] ?? false)) {
           return;
        }

        $this->framework->initialize();

        if (!$this->database->tableExists($table)) {
            return;
        }

        if (!isset($GLOBALS['TL_DCA'][$table]) || !isset($GLOBALS['TL_DCA'][$table]['config']['treeCache']) || !\is_array($GLOBALS['TL_DCA'][$table]['config']['treeCache'])) {
            return;
        }

        if ($this->utils->container()->isInstall() || !$this->requestStack->getCurrentRequest()) {
            return;
        }

        if (!$this->isCompleteInstallation($table)) {
            return;
        }

        $configurations = $GLOBALS['TL_DCA'][$table]['config']['treeCache'];

        foreach ($configurations as $key => $config) {
            $this->addConfigToTreeCache($table, $key, $config);
        }
    }

    public function addConfigToTreeCache(string $table, string $key, array $config = [])
    {
        $filename = $table.'_'.$key.'.php';

        if (file_exists($this->cacheDir . '/' . $filename)) {
            return;
        }

        if (null === ($roots = $this->utils->model()->findModelInstancesBy(
                $table,
                $config['columns'] ?? [],
                $config['values'] ?? [],
                $config['options'])
            )) {
            return;
        }

        $tree = $this->generateCacheTree($table, $roots->fetchEach($key), $key, $config);

        $this->filesystem->dumpFile(
            $this->cacheDir.'/'.$filename,
            sprintf("<?php\n\nreturn %s;\n", var_export($tree, true))
        );
    }

    /**
     * Get all child records for given parent entities.
     *
     * @param string $table     The database table
     * @param array  $ids       The parent entity ids
     * @param int    $maxLevels The max stop level
     * @param string Custom index key (default: primary key from model)
     * @param array $children Internal children return array
     * @param int   $level    Internal depth attribute
     *
     * @return array An array containing all children for given parent entities
     */
    public function getChildRecords(string $table, array $ids = [], $maxLevels = null, string $key = 'id', array $children = [], int $level = 0): array
    {
        if (null === ($tree = $this->getTreeCache($table, $key))) {
            return $this->database->getChildRecords($ids, $table);
        }

        foreach ($ids as $i => $id) {
            if (!isset($tree[$id]) || !\is_array($tree[$id])) {
                continue;
            }

            $children = array_merge($children, $tree[$id]);

            if (1 === $maxLevels) {
                continue;
            }

            if ($maxLevels > 0 && $level > $maxLevels) {
                return [];
            }

            if (!empty($nested = self::getChildRecords($table, $tree[$id], $maxLevels, $key, $children, ++$level))) {
                $children = $nested;
            } else {
                $depth = 0;
            }
        }

        return $children;
    }

    /**
     * Get all parent records for given child entity.
     *
     * @param string $table     The database table
     * @param int    $id        The current entity id
     * @param int    $maxLevels The max stop level
     * @param string Custom index key (default: primary key from model)
     * @param array $parents Internal children return array
     * @param int   $level   Internal depth attribute
     *
     * @return array An array containing all children for given parent entities
     */
    public function getParentRecords(string $table, int $id, $maxLevels = null, string $key = 'id', array $parents = [], int $level = 0): array
    {
        if (null === ($tree = $this->getTreeCache($table, $key))) {
            return $this->database->getParentRecords($id, $table);
        }

        if (isset($tree[$id]) && 0 === $level) {
            $parents[] = $id;
        }

        foreach ($tree as $pid => $ids) {
            if (!\in_array($id, $ids)) {
                continue;
            }

            $parents[] = $pid;

            if (1 === $maxLevels) {
                continue;
            }

            if ($maxLevels > 0 && $level > $maxLevels) {
                return [];
            }

            if (!empty($nested = self::getParentRecords($table, $pid, $maxLevels, $key, $parents, ++$level))) {
                $parents = $nested;
            } else {
                $level = 0;
            }
        }

        return $parents;
    }

    /**
     * Get the tree cache for a given table and key.
     *
     * @param string $table The database table
     * @param string Custom index key (default: primary key from model)
     */
    public function getTreeCache($table, $key): ?array
    {
        $filename = $table.'_'.$key.'.php';

        if (file_exists($this->cacheDir.'/'.$filename)) {
            self::$cache[$table.'_'.$key] = (include $this->cacheDir.'/'.$filename);
        }

        return self::$cache[$table.'_'.$key] ?? null;
    }

    /**
     * Generate the flat cache tree.
     *
     * @param string $table  The database table
     * @param string $key    Custom index key (default: primary key from model)
     * @param array  $ids    Root identifiers (parent ids)
     * @param array  $config Tree config
     * @param array  $return Internal return array
     *
     * @return array The flat cache tree
     */
    public function generateCacheTree(string $table, array $ids = [], string $key = 'id', array $config = [], $return = []): array
    {
        foreach ($ids as $id) {
            if (null === ($children = $this->utils->model()->findModelInstancesBy($table, [$table.'.pid = ?'], $id, $config['options']))) {
                $return[$id] = [];

                continue;
            }

            while ($children->next()) {
                $return[$children->pid][$children->{$key}] = $children->{$key};
                $return = $this->generateCacheTree($table, [$children->{$key}], $key, $config, $return);
            }
        }

        return $return;
    }

    /**
     * Generate all cache trees.
     *
     * @param $cacheDir
     */
    public function generateAllCacheTree($cacheDir)
    {
        $this->cacheDir = $cacheDir;

        $tables = $this->database->listTables();

        foreach ($tables as $table) {
            // trigger loadDataContainer TL_HOOK
            Controller::loadDataContainer($table);
        }
    }

    /**
     * Register a dca to the tree cache.
     *
     * @param string $table   (The dca table)
     * @param array  $columns Parent sql filter columns (e.g. `tl_page.type`)
     * @param array  $values  Parent sql filter values (e.g. `root` for `tl_page.type`)
     * @param array  $options SQL Options for sorting
     * @param string Custom index key (default: primary key from model)
     *
     * @return bool Acknowledge state if register succeeded
     */
    public function registerDcaToCacheTree(string $table, array $columns = [], array $values = [], array $options = [], string $key = 'id')
    {
        Controller::loadDataContainer($table);

        if (!isset($GLOBALS['TL_DCA'][$table])) {
            return false;
        }

        $GLOBALS['TL_DCA'][$table]['config']['treeCache'][$key] = [
            'columns' => $columns,
            'values' => $values,
            'options' => $options,
            'key' => $key,
        ];

        $GLOBALS['TL_DCA'][$table]['config']['ondelete_callback']['huh.utils.cache.database_tree'] = ['huh.utils.cache.database_tree', 'purgeCacheTree'];
        $GLOBALS['TL_DCA'][$table]['config']['oncut_callback']['huh.utils.cache.database_tree'] = ['huh.utils.cache.database_tree', 'purgeCacheTree'];
        $GLOBALS['TL_DCA'][$table]['config']['onsubmit_callback']['huh.utils.cache.database_tree'] = ['huh.utils.cache.database_tree', 'purgeCacheTree'];
        $GLOBALS['TL_DCA'][$table]['config']['onrestore_callback']['huh.utils.cache.database_tree'] = ['huh.utils.cache.database_tree', 'purgeCacheTree'];

        return true;
    }

    /**
     * Purge the tree cache completely in order to take table relations into consideration.
     */
    public function purgeCacheTree()
    {
        $this->filesystem->remove($this->cacheDir);
    }

    private function isCompleteInstallation($table)
    {
        try {
            $this->utils->model()->findOneModelInstanceBy($table, [], []);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}

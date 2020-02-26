<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Cache;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\StringUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DatabaseCacheUtil
{
    const DEFAULT_MAX_CACHE_TIME = ['unit' => 'd', 'value' => 1];

    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var Database
     */
    protected $database;
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->framework = $container->get('contao.framework');
        $this->database = $this->framework->createInstance(Database::class);
        $this->container = $container;
    }

    /**
     * Check for a given cache key.
     */
    public function keyExists(string $key): bool
    {
        $result = $this->database->prepare('SELECT * FROM tl_db_cache WHERE cacheKey = ?')->execute($key);

        return $result->numRows > 0;
    }

    /**
     * Retrieve a value from cache.
     *
     * @return mixed
     */
    public function getValue(string $key)
    {
        if (!Config::get('activateDbCache')) {
            return false;
        }

        // clean expired values at first (self-purification)
        $this->database->prepare('DELETE FROM tl_db_cache WHERE expiration < ?')->execute(time());

        $result = $this->database->prepare('SELECT * FROM tl_db_cache WHERE cacheKey = ?')->execute($key);

        if ($result->numRows > 0) {
            return $result->cacheValue;
        }

        return false;
    }

    /**
     * Store a given value to cache.
     *
     * @param $value
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function cacheValue(string $key, $value)
    {
        if (!Config::get('activateDbCache')) {
            return false;
        }

        if ($this->getValue($key)) {
            throw new \Exception('Duplicate entry in tl_db_cache for key '.$key);
        }

        $now = time();

        $this->database->prepare('INSERT INTO tl_db_cache (tstamp, expiration, cacheKey, cacheValue) VALUES (?, ?, ?, ?)')->execute(
            $now,
            $now + $this->container->get('huh.utils.date')->getTimePeriodInSeconds(
                StringUtil::deserialize(Config::get('dbCacheMaxTime'), true)
            ),
            $key,
            $value
        );

        return true;
    }
}

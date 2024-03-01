<?php

namespace HeimrichHannot\UtilsBundle\Dca;

abstract class AbstractDcaField
{
    /**
     * Register a dca to have an author field and update logic added.
     */
    public static function register(string $table): DcaFieldConfiguration
    {
        $config = static::createOptionObject($table);
        static::storeConfig($config);
        return $config;
    }

    abstract protected static function storeConfig(DcaFieldConfiguration $config): void;

    abstract protected static function loadConfig(): array;

    /**
     * @return array<DcaFieldConfiguration>
     */
    public static function getRegistrations(): array
    {
        return static::loadConfig();
    }

    protected static function createOptionObject(string $table): DcaFieldConfiguration
    {
        return new DcaFieldConfiguration($table);
    }
}
<?php

namespace HeimrichHannot\UtilsBundle\Dca;

abstract class AbstractDcaField
{
    protected static $tables = [];

    /**
     * Register a dca to have an author field and update logic added.
     */
    public static function register(string $table): DcaFieldOptions
    {
        $config = self::createOptionObject($table);

        static::$tables[$table] = $config;

        return $config;
    }

    /**
     * @return array<DcaFieldOptions>
     */
    public static function getRegistrations(): array
    {
        return static::$tables;
    }

    protected static function createOptionObject(string $table): DcaFieldOptions
    {
        return new DcaFieldOptions($table);
    }
}
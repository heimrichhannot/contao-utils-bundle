<?php

namespace HeimrichHannot\UtilsBundle\Dca;

class DateAddedField extends AbstractDcaField
{
    private static array $tables = [];

    protected static function storeConfig(DcaFieldConfiguration $config): void
    {
        static::$tables[$config->getTable()] = $config;
    }

    protected static function loadConfig(): array
    {
        return static::$tables;
    }
}
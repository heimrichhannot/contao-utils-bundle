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

    protected static function createOptionObject(string $table): DcaFieldConfiguration
    {
        $options = parent::createOptionObject($table);
        $options->setSorting(true);
        return $options;
    }
}
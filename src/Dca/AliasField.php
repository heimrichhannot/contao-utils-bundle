<?php

namespace HeimrichHannot\UtilsBundle\Dca;

class AliasField extends AbstractDcaField
{
    private static $tables = [];

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
        return new AliasFieldConfiguration($table);
    }

    public static function getField(): array
    {
        return [
            'label' => &$GLOBALS['TL_LANG']['MSC']['alias'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'alias', 'unique' => true, 'maxlength' => 128, 'tl_class' => 'w50'],
            'save_callback' => [],
            'sql' => "varchar(255) BINARY NOT NULL default ''",
        ];
    }
}
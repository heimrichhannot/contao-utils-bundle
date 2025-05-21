<?php

namespace HeimrichHannot\UtilsBundle\Dca;

class AliasField extends AbstractDcaField
{
    private static $tables = [];

    /**
     * @return AliasFieldConfiguration
     */
    public static function register(string $table): DcaFieldConfiguration
    {
        return parent::register($table);
    }


    protected static function storeConfig(DcaFieldConfiguration $config): void
    {
        self::$tables[$config->getTable()] = $config;
    }

    protected static function loadConfig(): array
    {
        return self::$tables;
    }

    protected static function createOptionObject(string $table): DcaFieldConfiguration
    {
        return new AliasFieldConfiguration($table);
    }

    public static function getField(): array
    {
        return [
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => [
                'rgxp' => 'alias',
                'unique' => true,
                'maxlength' => 128,
                'tl_class' => 'w50',
                'doNotCopy'=>true,
            ],
            'save_callback' => [],
            'sql' => "varchar(255) BINARY NOT NULL default ''",
        ];
    }
}
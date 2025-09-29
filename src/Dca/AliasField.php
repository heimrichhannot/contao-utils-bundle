<?php

namespace HeimrichHannot\UtilsBundle\Dca;

/**
 * @method static array<AliasFieldConfiguration> getRegistrations()
 * @method static AliasFieldConfiguration register(string $table)
 */
class AliasField extends AbstractDcaField
{
    private static array $tables = [];

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
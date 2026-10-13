<?php

namespace HeimrichHannot\UtilsBundle\Dca;

/**
 * Adds a field holding the normalized and stemmed values of other fields, so records can be searched with
 * SearchVectorQuery.
 */
class SearchVectorField extends AbstractDcaField
{
    protected static array $tables = [];

    /**
     * @return SearchVectorFieldConfiguration
     */
    public static function register(string $table): DcaFieldConfiguration
    {
        return parent::register($table);
    }

    /**
     * @return array<string, SearchVectorFieldConfiguration>
     */
    public static function getRegistrations(): array
    {
        return parent::getRegistrations();
    }

    protected static function createOptionObject(string $table): DcaFieldConfiguration
    {
        $options = new SearchVectorFieldConfiguration($table);
        $options->setExclude(true);

        return $options;
    }

    protected static function storeConfig(DcaFieldConfiguration $config): void
    {
        static::$tables[$config->getTable()] = $config;
    }

    protected static function loadConfig(): array
    {
        return static::$tables;
    }
}

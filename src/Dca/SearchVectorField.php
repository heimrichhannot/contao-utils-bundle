<?php

namespace HeimrichHannot\UtilsBundle\Dca;

/**
 * Adds a field holding the normalized and stemmed values of other fields, so records can be searched with
 * SearchVectorQuery.
 *
 * Only one search vector field per table is supported. Registering a second one throws an exception when the DCA of the
 * table is loaded.
 */
class SearchVectorField extends AbstractDcaField
{
    protected static array $tables = [];

    /**
     * Registrations since the DCA of a table was loaded the last time, to detect several registrations in one DCA.
     *
     * @var array<string, list<SearchVectorFieldConfiguration>>
     */
    protected static array $pendingRegistrations = [];

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

    /**
     * Returns the registrations of a table since the last call and forgets them.
     *
     * A DCA can be loaded several times in one process, each time registering the field again, so several
     * registrations only conflict if they happen while loading the DCA once.
     *
     * @internal
     *
     * @return list<SearchVectorFieldConfiguration>
     */
    public static function takePendingRegistrations(string $table): array
    {
        $registrations = static::$pendingRegistrations[$table] ?? [];
        unset(static::$pendingRegistrations[$table]);

        return $registrations;
    }

    protected static function storeConfig(DcaFieldConfiguration $config): void
    {
        static::$tables[$config->getTable()] = $config;
        static::$pendingRegistrations[$config->getTable()][] = $config;
    }

    protected static function loadConfig(): array
    {
        return static::$tables;
    }
}

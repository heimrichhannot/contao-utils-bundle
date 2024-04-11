<?php

namespace HeimrichHannot\UtilsBundle\Dca;

class AuthorField extends AbstractDcaField
{
    public const TYPE_USER = 'user';
    public const TYPE_MEMBER = 'member';

    protected static array $tables = [];

    /**
     * @return array<AuthorFieldConfiguration>
     */
    public static function getRegistrations(): array
    {
        return parent::getRegistrations();
    }

    protected static function createOptionObject(string $table): DcaFieldConfiguration|AuthorFieldConfiguration
    {
        $options = new AuthorFieldConfiguration($table);

        $options->setExclude(true);
        $options->setSearch(true);
        $options->setFilter(true);

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
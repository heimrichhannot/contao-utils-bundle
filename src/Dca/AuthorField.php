<?php

namespace HeimrichHannot\UtilsBundle\Dca;

/**
 * @method static array<AuthorFieldConfiguration> getRegistrations()
 * @method static AuthorFieldConfiguration register(string $table)
 */
class AuthorField extends AbstractDcaField
{
    public const TYPE_USER = 'user';
    public const TYPE_MEMBER = 'member';

    protected static array $tables = [];

    /**
     * @param string $table
     * @return AuthorFieldConfiguration
     */
    protected static function createOptionObject(string $table): DcaFieldConfiguration
    {
        return new AuthorFieldConfiguration($table);
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
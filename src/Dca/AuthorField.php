<?php

namespace HeimrichHannot\UtilsBundle\Dca;

class AuthorField
{
    public const TYPE_USER = 'user';
    public const TYPE_MEMBER = 'member';

    /** @var array  */
    protected static $tables = [];

    /**
     * Register a dca to have an author field and update logic added.
     */
    public static function register(string $table): AuthorFieldOptions
    {
        $config = new AuthorFieldOptions($table);

        static::$tables[$table] = $config;

        return $config;
    }

    /**
     * @return array<AuthorFieldOptions>
     */
    public static function getRegistrations(): array
    {
        return static::$tables;
    }
}
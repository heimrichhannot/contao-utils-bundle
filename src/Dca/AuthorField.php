<?php

namespace HeimrichHannot\UtilsBundle\Dca;

class AuthorField
{
    public const TYPE_USER = 'user';
    public const TYPE_MEMBER = 'member';

    protected static array $tables = [];

    /**
     * Register a dca to have an author field and update logic added.
     *
     * @param string $table
     * @param array{
     *     type?: string,
     *     fieldNamePrefix?: string,
     *     useDefaultLabel?: bool,
     *     exclude?: bool,
     *     search?: bool,
     *     filter?: bool,
     * } $options
     * @return void
     */
    public static function register(string $table, array $options = []): void
    {
        static::$tables[$table] = $options;
    }

    public static function getRegistrations(): array
    {
        return static::$tables;
    }
}
<?php

namespace HeimrichHannot\UtilsBundle\StaticUtil;

class SUtils
{
    protected static array $instances = [];

    public static function array(): StaticArrayUtil
    {
        return static::getInstance(StaticArrayUtil::class);
    }

    public static function class(): StaticClassUtil
    {
        return static::getInstance(StaticClassUtil::class);
    }

    /**
     * @template T
     * @param class-string<T> $class
     * @return T The instance of the given class.
     */
    protected static function getInstance(string $class): object
    {
        if (!isset(static::$instances[$class])) {
            static::$instances[$class] = new $class;
        }

        return static::$instances[$class];
    }
}
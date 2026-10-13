<?php

namespace HeimrichHannot\UtilsBundle\StaticUtil;

final class SUtils
{
    private static array $instances = [];

    public static function array(): StaticArrayUtil
    {
        return SUtils::getInstance(StaticArrayUtil::class);
    }

    public static function class(): StaticClassUtil
    {
        return SUtils::getInstance(StaticClassUtil::class);
    }

    public static function url(): StaticUrlUtil
    {
        return SUtils::getInstance(StaticUrlUtil::class);
    }

    /**
     * @template T
     * @param class-string<T> $class
     * @return T The instance of the given class.
     */
    protected static function getInstance(string $class)
    {
        SUtils::$instances[$class] ??= new $class;

        return SUtils::$instances[$class];
    }
}
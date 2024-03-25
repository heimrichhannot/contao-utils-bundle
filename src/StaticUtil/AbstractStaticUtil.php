<?php

namespace HeimrichHannot\UtilsBundle\StaticUtil;

/**
 * @internal
 * @codeCoverageIgnore
 */
abstract class AbstractStaticUtil
{
    public function __call(string $name, array $arguments)
    {
        return static::$name(...$arguments);
    }
}
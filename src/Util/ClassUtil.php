<?php

namespace HeimrichHannot\UtilsBundle\Util;

use HeimrichHannot\UtilsBundle\StaticUtil\StaticClassUtil;

class ClassUtil
{
    /**
     * Return true if the given class or a parent class implements the given trait
     *
     * @deprecated Use {@see StaticClassUtil::hasTrait() SUtil::class()->hasTrait(...)} instead.
     */
    public function classImplementsTrait(object|string $class, string $trait): bool
    {
        return StaticClassUtil::hasTrait($class, $trait);
    }
}
<?php

namespace HeimrichHannot\UtilsBundle\Util;

class ClassUtil
{
    /**
     * Return true if the given class or a parent class implements the given trait
     */
    public function classImplementsTrait(object|string $class, string $trait): bool
    {
        do {
            if (in_array($trait, class_uses($class))) {
                return true;
            }

        } while($class = get_parent_class($class));

        return false;
    }
}
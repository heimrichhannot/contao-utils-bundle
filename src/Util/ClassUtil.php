<?php

namespace HeimrichHannot\UtilsBundle\Util;

class ClassUtil
{
    /**
     * Return true if the given class or a parent class implements the given trait
     * @param object|string $class
     * @param string $trait
     * @return bool
     */
    public function classImplementsTrait($class, string $trait): bool
    {
        do {
            if (in_array($trait, class_uses($class))) {
                return true;
            }

        } while($class = get_parent_class($class));

        return false;
    }
}
<?php

namespace HeimrichHannot\UtilsBundle\StaticUtil;

class StaticClassUtil extends AbstractStaticUtil
{
    /**
     * Check if a class or any of its parents implements a trait.
     *
     * @param object|class-string $class The class to check.
     * @param class-string $trait The trait to check for.
     * @return bool True if the class or any of its parents implements the trait, false otherwise.
     */
    public static function hasTrait(object|string $class, string $trait): bool
    {
        do {
            if (in_array($trait, class_uses($class))) {
                return true;
            }
        } while ($class = get_parent_class($class));

        return false;
    }
}
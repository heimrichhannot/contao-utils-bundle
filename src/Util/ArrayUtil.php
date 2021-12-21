<?php

namespace HeimrichHannot\UtilsBundle\Util;

class ArrayUtil
{
    /**
     * Insert a new entry before a specific or multiple keys in array.
     * If the keys not exist, the new entry is added to the end of the array.
     * Array is passed as reference.
     *
     * Usage example: contao config.php to make your hook entry run before another.
     *
     * @param array $array Array the new entry should inserted to
     * @param string|array $keys The key or keys where the new entry should be added before
     * @param string $newKey The key of the entry that should be added
     * @param mixed $newValue The value of the entry that should be added
     */
    public static function insertBeforeKey(array &$array, $keys, string $newKey, $newValue)
    {
        if (!is_array($keys) && !is_string($keys)) {
            throw new \InvalidArgumentException('Parameter $key must be of type array or string.');
        }

        if (!is_array($keys)) {
            $keys = [$keys];
        }

        if (array_intersect($keys, array_keys($array))) {
            $new = [];

            foreach ($array as $k => $value) {
                if (in_array($k, $keys)) {
                    $new[$newKey] = $newValue;
                }
                $new[$k] = $value;
            }
            $array = $new;
        } else {
            $array[$newKey] = $newValue;
        }
    }
}
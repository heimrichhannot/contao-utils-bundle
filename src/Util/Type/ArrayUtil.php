<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util\Type;

class ArrayUtil
{
    /**
     * Insert a new entry before a specific or multiple keys in array.
     * If the keys not exist, the new entry is added to the end of the array.
     * Array is passed as reference.
     *
     * @param array        $array    Array the new entry should inserted to
     * @param string|array $keys     The key or keys where the new entry should be added before
     * @param string       $newKey   The key of the entry that should be added
     * @param mixed        $newValue The value of the entry that should be added
     */
    public static function insertBeforeKey(array &$array, $keys, string $newKey, $newValue)
    {
        if (!\is_array($keys) && !\is_string($keys)) {
            throw new \InvalidArgumentException('Parameter $key must be of type array or string.');
        }

        if (!\is_array($keys)) {
            $keys = [$keys];
        }

        if (array_intersect($keys, array_keys($array))) {
            $new = [];

            foreach ($array as $k => $value) {
                if (\in_array($k, $keys)) {
                    $new[$newKey] = $newValue;
                }
                $new[$k] = $value;
            }
            $array = $new;
        } else {
            $array[$newKey] = $newValue;
        }
    }

    /**
     * Insert a value into an existing array by key name.
     *
     * Additional options:
     * - (bool) strict: Strict behavior for array search. Default false
     * - (bool) attachIfKeyNotExist: Attach value at the end of the array if key not exist. Default: true
     * - (int) offset: Add an additional offset
     *
     * @param array  $array   The target array
     * @param string $key     the existing target key in the array
     * @param mixed  $value   the new value to be inserted
     * @param array  $options Additional options
     */
    public function insertAfterKey(array &$array, string $key, $value, string $newKey = null, array $options = []): void
    {
        $options = array_merge([
            'strict' => false,
            'attachIfKeyNotExist' => true,
            'offset' => 0,
        ], $options);

        $keys = array_keys($array);
        $index = array_search($key, $keys, $options['strict']);

        if (false === $index && false === $options['attachIfKeyNotExist']) {
            return;
        }
        $pos = false === $index ? \count($array) : $index + 1;
        $pos = $pos + $options['offset'];

        if ($newKey) {
            $value = [$newKey => $value];
        } else {
            $value = [$value];
        }

        $array = array_combine(
            array_merge(\array_slice($keys, 0, $pos), array_keys($value), \array_slice($keys, $pos)),
            array_merge(\array_slice($array, 0, $pos), $value, \array_slice($array, $pos))
        );
    }

    /**
     * Removes a value in an array.
     *
     * @param $value
     *
     * @return bool Returns true if the value has been found and removed, false in other cases
     */
    public function removeValue($value, array &$array): bool
    {
        if (false !== ($intPosition = array_search($value, $array))) {
            unset($array[$intPosition]);

            return true;
        }

        return false;
    }
}

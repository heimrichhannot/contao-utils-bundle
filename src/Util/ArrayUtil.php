<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util;

use HeimrichHannot\UtilsBundle\StaticUtil\StaticArrayUtil;

class ArrayUtil
{
    /**
     * Insert a new entry before a specific or multiple keys in array.
     * If the keys not exist, the new entry is added to the end of the array.
     * Array is passed as reference.
     *
     * @param array        $array    Array the new entry should inserted to
     * @param array|string $keys     The key or keys where the new entry should be added before
     * @param string       $newKey   The key of the entry that should be added
     * @param mixed        $newValue The value of the entry that should be added
     *
     * @deprecated Use {@see StaticArrayUtil::insertBeforeKey() SUtil::array()->insertBeforeKey(...)} instead.
     */
    public static function insertBeforeKey(array &$array, array|string $keys, string $newKey, mixed $newValue): void
    {
        StaticArrayUtil::insertBeforeKey($array, $keys, $newKey, $newValue);
    }

    /**
     * Insert a value into an existing array by key name.
     *
     * Additional options:
     * - (bool) strict: Strict behavior for array search. Default false
     * - (bool) attachIfKeyNotExist: Attach value at the end of the array if key not exist. Default: true
     * - (int) offset: Add additional offset
     *
     * @param array  $array   The target array
     * @param string $key     the existing target key in the array
     * @param mixed  $value   the new value to be inserted
     * @param array{
     *     strict?: bool,
     *     attachIfKeyNotExist?: bool,
     *     offset?: int
     * } $options Additional options
     *
     * @deprecated Use {@see StaticArrayUtil::insertAfterKey() SUtil::array()->insertAfterKey(...)} instead.
     *             Beware: The option keys have changed!
     */
    public function insertAfterKey(array &$array, string $key, mixed $value, string $newKey = null, array $options = []): void
    {
        $options['attachMissingKey'] ??= $options['attachIfKeyNotExist'] ?? true;

        StaticArrayUtil::insertAfterKey($array, $key, $value, $newKey, $options);
    }

    /**
     * Removes a value from an array.
     *
     * @return bool True if the value was found and removed, false otherwise.
     *
     * @deprecated Use {@see StaticArrayUtil::removeValue() SUtil::array()->removeValue(...)} instead.
     */
    public function removeValue(mixed $value, array &$array): bool
    {
        return StaticArrayUtil::removeValue($value, $array);
    }
}

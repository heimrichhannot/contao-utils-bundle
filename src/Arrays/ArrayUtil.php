<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Arrays;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;

class ArrayUtil
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Filter an Array by given prefixes.
     *
     * @param array $data
     * @param array $prefixes
     *
     * @return array the filtered array or $arrData if $prefix is empty
     */
    public function filterByPrefixes(array $data = [], $prefixes = [])
    {
        $extract = [];

        if (!is_array($prefixes) || empty($prefixes)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            foreach ($prefixes as $prefix) {
                if (System::getContainer()->get('huh.utils.string')->startsWith($key, $prefix)) {
                    $extract[$key] = $value;
                }
            }
        }

        return $extract;
    }

    /**
     * sort an array alphabetically by some key in the second layer (x => array(key1, key2, key3)).
     *
     * @param array $array
     */
    public function aasort(array &$array, $key)
    {
        $sorter = [];
        $ret = [];
        reset($array);

        foreach ($array as $ii => $va) {
            $sorter[$ii] = $va[$key];
        }

        asort($sorter);

        foreach ($sorter as $ii => $va) {
            $ret[$ii] = $array[$ii];
        }

        $array = $ret;
    }

    /**
     * Removes a value in an array.
     *
     * @param       $value
     * @param array $array
     *
     * @return bool Returns true if the value has been found and removed, false in other cases
     */
    public function removeValue($value, array &$array): bool
    {
        if (false !== ($intPosition = array_search($value, $array, true))) {
            unset($array[$intPosition]);

            return true;
        }

        return false;
    }

    public function removePrefix(string $prefix, array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $result[System::getContainer()->get('huh.utils.string')->removeLeadingString($prefix, $key)] = $value;
        }

        return $result;
    }

    /**
     * @param array  $arrOld
     * @param string $key
     * @param array  $new
     * @param int    $offset
     * @param bool   $strict
     */
    public function insertInArrayByName(array &$arrOld, string $key, array $new, int $offset = 0, bool $strict = false)
    {
        if (false !== ($intIndex = array_search($key, array_keys($arrOld), $strict))) {
            array_insert($arrOld, $intIndex + $offset, $new);
        }
    }

    /**
     * creates a stdClass from array.
     *
     * @param $array
     *
     * @return \stdClass
     */
    public function arrayToObject(array $array): \stdClass
    {
        $objResult = new \stdClass();
        foreach ($array as $varKey => $varValue) {
            $objResult->{$varKey} = $varValue;
        }

        return $objResult;
    }

    /**
     * Returns a row of an multidimensional array by field value. Returns false, if no row found.
     *
     * @param string|int $key        The array key (fieldname)
     * @param mixed      $value
     * @param array      $haystack   a multidimensional array
     * @param bool       $strictType Specifiy if type comparison should be strict (type-safe)
     *
     * @return mixed
     */
    public function getArrayRowByFieldValue($key, $value, array $haystack, bool $strictType = false)
    {
        foreach ($haystack as $row) {
            if (!is_array($row)) {
                continue;
            }
            if (!isset($row[$key])) {
                continue;
            }
            if (true === $strictType) {
                if ($value === $row[$key]) {
                    return $row;
                }
            } else {
                if ($row[$key] == $value) {
                    return $row;
                }
            }
        }

        return false;
    }

    /**
     * Flattens an multidimensional array to one dimension. Keys are not preserved.
     *
     * @param array $array
     *
     * @return array
     */
    public function flattenArray(array $array)
    {
        $return = [];
        array_walk_recursive(
            $array,
            function ($a) use (&$return) {
                $return[] = $a;
            }
        );

        return $return;
    }
}

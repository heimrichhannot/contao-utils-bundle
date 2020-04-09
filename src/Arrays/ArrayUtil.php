<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Arrays;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\StringUtil;
use Contao\Validator;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ArrayUtil
{
    /**
     * @var ContaoFramework
     */
    protected $framework;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->framework = $container->get('contao.framework');
        $this->container = $container;
    }

    /**
     * Filter an Array by given prefixes.
     *
     * @param array $prefixes
     *
     * @return array the filtered array or $arrData if $prefix is empty
     */
    public function filterByPrefixes(array $data = [], $prefixes = [])
    {
        $extract = [];

        if (!\is_array($prefixes) || empty($prefixes)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            foreach ($prefixes as $prefix) {
                if ($this->container->get('huh.utils.string')->startsWith($key, $prefix)) {
                    $extract[$key] = $value;
                }
            }
        }

        return $extract;
    }

    /**
     * sort an array alphabetically by some key in the second layer (x => array(key1, key2, key3)).
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

    public function removePrefix(string $prefix, array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $result[$this->container->get('huh.utils.string')->removeLeadingString($prefix, $key)] = $value;
        }

        return $result;
    }

    /**
     * Insert a value into an existing array by key name.
     *
     * @param array  $current The target array
     * @param string $key     the existing target key in the array
     * @param mixed  $value   the new value to be inserted
     * @param int    $offset  offset for inserting the new value
     * @param bool   $strict  use strict behavior for array search
     */
    public function insertInArrayByName(array &$current, string $key, $value, int $offset = 0, bool $strict = false)
    {
        if (false !== ($intIndex = array_search($key, array_keys($current), $strict))) {
            array_insert($current, $intIndex + $offset, $value);
        }
    }

    /**
     * Creates a stdClass from array.
     *
     * @param $array
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
            if (!\is_array($row)) {
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

    /**
     * Insert a new entry before an specific key in array.
     * If key not exist, the new entry is added to the end of the array.
     * Array is passed as reference.
     *
     * Usage example: contao config.php to make your hook entry run before another.
     *
     * @param array  $array    Array the new entry should inserted to
     * @param string $key      The key where the new entry should be added before
     * @param string $newKey   The key of the entry that should be added
     * @param mixed  $newValue The value of the entry that should be added
     */
    public static function insertBeforeKey(array &$array, string $key, string $newKey, $newValue)
    {
        if (\array_key_exists($key, $array)) {
            $new = [];

            foreach ($array as $k => $value) {
                if ($k === $key) {
                    $new[$newKey] = $newValue;
                }
                $new[$k] = $value;
            }
            $array = $new;
        } else {
            $array[$newKey] = $newValue;
        }
    }

    public function implodeRecursive($var, $binary = false)
    {
        if (!\is_array($var)) {
            return $binary ? StringUtil::binToUuid($var) : $var;
        }

        if (!\is_array(current($var))) {
            if ($binary) {
                $var = array_map(function ($v) {
                    return $v ? (Validator::isBinaryUuid($v) ? StringUtil::binToUuid($v) : $v) : '';
                }, $var);
            }

            return implode(', ', $var);
        }

        $buffer = '';

        foreach ($var as $k => $v) {
            $buffer .= $k.': '.$this->implodeRecursive($v)."\n";
        }

        return trim($buffer);
    }
}

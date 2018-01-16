<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\UtilsBundle\Arrays;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;

class ArrayUtil
{
    /** @var ContaoFrameworkInterface */
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
    public static function aasort(&$array, $key)
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
}

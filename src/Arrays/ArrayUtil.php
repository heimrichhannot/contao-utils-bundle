<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\UtilsBundle\Arrays;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use HeimrichHannot\UtilsBundle\String\StringUtil;

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
    public static function filterByPrefixes(array $data = [], $prefixes = [])
    {
        $extract = [];

        if (!is_array($prefixes) || empty($prefixes)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            foreach ($prefixes as $prefix) {
                if (StringUtil::startsWith($key, $prefix)) {
                    $extract[$key] = $value;
                }
            }
        }

        return $extract;
    }
}

<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util\Dca;

use Contao\StringUtil;

class DcaUtil
{
    /**
     * Explode a palette string.
     */
    public function explodePalette(string $palette): array
    {
        $boxes = StringUtil::trimsplit(';', $palette);
        $legends = [];

        if (!empty($boxes)) {
            foreach ($boxes as $k => $v) {
                $eCount = 1;
                $boxes[$k] = StringUtil::trimsplit(',', $v);

                foreach ($boxes[$k] as $kk => $vv) {
                    if (preg_match('/^\[.*\]$/', $vv)) {
                        ++$eCount;

                        continue;
                    }

                    if (preg_match('/^\{.*\}$/', $vv)) {
                        $legends[$k] = substr($vv, 1, -1);
                        unset($boxes[$k][$kk]);
                    }
                }

                // Unset a box if it does not contain any fields
                if (\count($boxes[$k]) < $eCount) {
                    unset($boxes[$k]);
                }
            }
        }

        $arrFields = [];

        if (!\is_array($boxes)) {
            return $arrFields;
        }

        // flatten
        array_walk_recursive(
            $boxes,
            function ($a) use (&$arrFields) {
                $arrFields[] = $a;
            }
        );

        // remove empty values
        return array_filter($arrFields);
    }
}

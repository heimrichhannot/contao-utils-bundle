<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util\Dca;

use Contao\Controller;
use Contao\CoreBundle\DataContainer\PaletteNotFoundException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\StringUtil;

class DcaUtil
{
    /**
     * @var ContaoFramework
     */
    private $contaoFramework;

    public function __construct(ContaoFramework $contaoFramework)
    {
        $this->contaoFramework = $contaoFramework;
    }

    /**
     * Return all fields of a palette including its subpalettes as array.
     *
     * Options:
     * * skip_subpalettes (bool): Don't add subpalette fields to result.
     */
    public function getPaletteFields(string $table, string $palette, array $options = []): array
    {
        $options = array_merge([
            'skip_subpalettes' => false,
        ], $options);

        $this->contaoFramework->getAdapter(Controller::class)->loadDataContainer($table);

        if (!isset($GLOBALS['TL_DCA'][$table])) {
            throw new PaletteNotFoundException(sprintf('Table "%s" not found', $table));
        }

        $dca = &$GLOBALS['TL_DCA'][$table];

        if (!isset($dca['palettes'][$palette])) {
            throw new PaletteNotFoundException(sprintf('Palette "%s" not found in table "%s"', $palette, $table));
        }

        $fields = $this->explodePalette($dca['palettes'][$palette]);

        if (true === $options['skip_subpalettes']) {
            return $fields;
        }

        $processed = [];

        $iterator = new \ArrayIterator($fields);

        while ($iterator->valid()) {
            if (\in_array($iterator->current(), $processed)) {
                $iterator->next();

                continue;
            }
            $processed[] = $iterator->current();

            if (\in_array($iterator->current(), $dca['palettes']['__selector__']) && isset($dca['subpalettes'][$iterator->current()])) {
                $subpaletteFields = $this->explodePalette($dca['subpalettes'][$iterator->current()]);

                foreach ($subpaletteFields as $subpaletteField) {
                    $iterator->append($subpaletteField);
                }
            }
            $iterator->next();
        }

        return $processed;
    }

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

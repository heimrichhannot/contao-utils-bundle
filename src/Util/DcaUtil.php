<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util;

use Contao\Controller;
use Contao\CoreBundle\DataContainer\PaletteNotFoundException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\StringUtil;
use HeimrichHannot\UtilsBundle\Util\DcaUtil\GetDcaFieldsOptions;

class DcaUtil
{

    public function __construct(
        private ContaoFramework $contaoFramework
    ) {
    }

    /**
     * Return all fields of a palette including its subpalettes as array.
     *
     * Options:
     * * skip_subpalettes (bool): Don't add subpalette fields to result. Default false
     *
     * @param array{
     *     skip_subpalettes?: bool
     * } $options
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

    /**
     * Return a list of dca fields for given table.
     * Fields can be filtered by given options.
     */
    public function getDcaFields(string $table, GetDcaFieldsOptions $options = new GetDcaFieldsOptions()): array
    {
        $fields = [];

        $controller = $this->contaoFramework->getAdapter(Controller::class);
        $controller->loadDataContainer($table);
        $controller->loadLanguageFile($table);

        if (!isset($GLOBALS['TL_DCA'][$table]['fields'])) {
            return $fields;
        }

        foreach ($GLOBALS['TL_DCA'][$table]['fields'] as $name => $data) {
            if ($options->isOnlyDatabaseFields()) {
                if (!isset($data['sql'])) {
                    continue;
                }
            }

            // restrict to certain input types
            if ($options->isOnlyAllowedInputTypes() && (!isset($data['inputType']) || !\in_array($data['inputType'], $options->getAllowedInputTypes()))) {
                continue;
            }

            // restrict to certain dca eval
            if ($options->isHasEvalConditions()) {
                foreach ($options->getEvalConditions() as $key => $value) {
                    if (!isset($data['eval'][$key]) || $data['eval'][$key] !== $value) {
                        continue 2;
                    }
                }
            }

            if (!$options->isLocalizeLabels()) {
                $fields[] = $name;
            } else {
                $fields[$name] = $data['label'][0] ?? $name;
            }
        }

        if (!$options->isSkipSorting()) {
            if ($options->isLocalizeLabels()) {
                asort($fields);
            } else {
                sort($fields);
            }
        }

        return $fields;
    }
}

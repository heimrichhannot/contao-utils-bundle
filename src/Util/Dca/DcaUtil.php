<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
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

    /**
     * Return a list of dca fields for given table.
     * Fields can be filtered by given options.
     *
     * Options:
     * - onlyDatabaseFields (bool): Return only fields with sql definition. Default false
     * - allowedInputTypes (array): Return only fields of given types.
     * - evalConditions (array): Return only fields with given eval key-value-pairs.
     * - localizeLabels (bool): Return also the field labels (key = field name, value = field label). Default false
     * - skipSorting (bool): Skip sorting fields by field name alphabetical. Default false
     */
    public function getDcaFields(string $table, array $options = []): array
    {
        $options = array_merge([
            'onlyDatabaseFields' => false,
            'allowedInputTypes' => [],
            'evalConditions' => [],
            'localizeLabels' => false,
            'skipSorting' => false,
        ], $options);

        if (!\is_array($options['allowedInputTypes'])) {
            $options['allowedInputTypes'] = [];
            trigger_error('DcaUtil::getDcaFields() option "allowedInputTypes" must be of type array!', \E_USER_WARNING);
        }

        if (!\is_array($options['evalConditions'])) {
            $options['evalConditions'] = [];
            trigger_error('DcaUtil::getDcaFields() option "evalConditions" must be of type array!', \E_USER_WARNING);
        }

        $fields = [];

        $controller = $this->contaoFramework->getAdapter(Controller::class);
        $controller->loadDataContainer($table);
        $controller->loadLanguageFile($table);

        if (!isset($GLOBALS['TL_DCA'][$table]['fields'])) {
            return $fields;
        }

        foreach ($GLOBALS['TL_DCA'][$table]['fields'] as $name => $data) {
            if ($options['onlyDatabaseFields']) {
                if (!isset($data['sql'])) {
                    continue;
                }
            }

            // restrict to certain input types
            if (!empty($options['allowedInputTypes']) && (!isset($data['inputType']) || !\in_array($data['inputType'], $options['allowedInputTypes']))) {
                continue;
            }

            // restrict to certain dca eval
            if (!empty($options['evalConditions'])) {
                foreach ($options['evalConditions'] as $key => $value) {
                    if (!isset($data['eval'][$key]) || $data['eval'][$key] !== $value) {
                        continue 2;
                    }
                }
            }

            if (!$options['localizeLabels']) {
                $fields[] = $name;
            } else {
                $fields[$name] = $data['label'][0] ?? $name;
            }
        }

        if (!$options['skipSorting']) {
            if ($options['localizeLabels']) {
                asort($fields);
            } else {
                sort($fields);
            }
        }

        return $fields;
    }

    /**
     * @param array|callable|null $callback
     * @param mixed ...$arguments
     * @return mixed
     */
    public function executeCallback($callback, ...$arguments)
    {
        if (!$callback) {
            return null;
        }

        if (is_array($callback))
        {
            if (!isset($callback[0]) || !isset($callback[1])) {
                return null;
            }

            try {
                /** @var Controller $controller */
                $controller = $this->contaoFramework->getAdapter(Controller::class);
                $instance = $controller->importStatic($callback[0]);
            } catch (\Exception $e) {
                return null;
            }

            if (!method_exists($instance, $callback[1])) {
                return null;
            }

            $callback = [$instance, $callback[1]];
        }
        elseif (!is_callable($callback))
        {
            return null;
        }

        try
        {
            return call_user_func_array($callback, $arguments);
        }
        catch (\Error $e)
        {
            return null;
        }
    }
}

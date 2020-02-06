<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Choice;

use Contao\System;
use HeimrichHannot\UtilsBundle\Driver\DC_Table_Utils;

class ModelInstanceChoice extends AbstractChoice
{
    const TITLE_FIELDS = [
        'name',
        'title',
    ];

    /**
     * @return array
     */
    protected function collect()
    {
        $context = $this->getContext();
        $choices = [];

        $instances = System::getContainer()->get('huh.utils.model')->findModelInstancesBy($context['dataContainer'], $context['columns'] ?? [], $context['values'] ?? null, isset($context['options']) ? (\is_array($context['options']) ? $context['options'] : []) : []);

        if (null === $instances) {
            return $choices;
        }

        while ($instances->next()) {
            $labelPattern = $context['labelPattern'] ?? null;

            if (!$labelPattern) {
                $labelPattern = 'ID %id%';

                switch ($context['dataContainer']) {
                    case 'tl_member':
                        $labelPattern = '%firstname% %lastname% (ID %id%)';

                        break;

                    default:
                        foreach (static::TITLE_FIELDS as $titleField) {
                            if (isset($GLOBALS['TL_DCA'][$context['dataContainer']]['fields'][$titleField])) {
                                $labelPattern = '%'.$titleField.'%';

                                break;
                            }
                        }

                        break;
                }
            }

            $skipFormatting = $context['skipFormatting'] ?? false;

            if (!$skipFormatting) {
                $dca = &$GLOBALS['TL_DCA']['tl_submission'];
                $dc = new DC_Table_Utils($context['dataContainer']);
                $dc->id = $instances->id;
                $dc->activeRecord = $instances->current();

                $label = preg_replace_callback(
                    '@%([^%]+)%@i',
                    function ($matches) use ($instances, $dca, $context, $dc) {
                        return System::getContainer()->get('huh.utils.form')->prepareSpecialValueForOutput(
                            $matches[1],
                            $instances->{$matches[1]},
                            $dc
                        );
                    },
                    $labelPattern
                );
            } else {
                $label = preg_replace_callback(
                    '@%([^%]+)%@i',
                    function ($matches) use ($instances) {
                        return $instances->{$matches[1]};
                    },
                    $labelPattern
                );
            }

            if (null !== ($callbackLabel = System::getContainer()->get('huh.utils.dca')->getConfigByArrayOrCallbackOrFunction($context, 'label', [$label, $instances->row(), $context]))) {
                $label = $callbackLabel;
            }

            $choices[$instances->id] = $label;
        }

        if (!isset($context['skipSorting']) || !$context['skipSorting']) {
            asort($choices);
        }

        return $choices;
    }
}

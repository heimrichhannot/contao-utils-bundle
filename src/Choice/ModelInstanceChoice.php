<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Choice;

use Contao\System;

class ModelInstanceChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $context = $this->getContext();
        $choices = [];

        $instances = System::getContainer()->get('huh.utils.model')->findModelInstancesBy(
            $context['dataContainer'],
            $context['columns'] ?: null,
            $context['values'] ?: null,
            is_array($context['options']) ? $context['options'] : []
        );

        if (null === $instances) {
            return $choices;
        }

        while ($instances->next()) {
            $label = $instances->id;

            if ($context['labelPattern']) {
                $label = preg_replace_callback(
                    '@%([^%]+)%@i',
                    function ($matches) use ($instances) {
                        return $instances->{$matches[1]};
                    },
                    $context['labelPattern']
                );
            }

            $choices[$instances->id] = $label;
        }

        if (!$context['skipSorting']) {
            asort($choices);
        }

        return $choices;
    }
}

<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\UtilsBundle\Choice;

use Contao\Controller;
use Contao\System;

class FieldChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $context = $this->getContext();

        $choices = [];

        if (!$context['dataContainer']) {
            return $choices;
        }

        $dataContainer = $context['dataContainer'];

        Controller::loadDataContainer($dataContainer);
        System::loadLanguageFile($dataContainer);

        if (!isset($GLOBALS['TL_DCA'][$dataContainer]['fields'])) {
            return $choices;
        }

        foreach ($GLOBALS['TL_DCA'][$dataContainer]['fields'] as $name => $data) {
            // restrict to certain input types
            if (is_array($context['inputTypes']) && !empty($context['inputTypes']) && !in_array($data['inputType'], $context['inputTypes'], true)) {
                continue;
            }

            if (!$context['localizeLabels']) {
                $choices[$name] = $name;
            } else {
                $choices[$name] = ($data['label'][0] ?: $name).($data['label'][0] ? ' ['.$name.']' : '');
            }
        }

        if ($context['sort']) {
            asort($choices);
        }

        return $choices;
    }
}

<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Choice;

use Contao\System;

class FieldChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $context = $this->getContext();

        return System::getContainer()->get('huh.utils.dca')->getFields($context['dataContainer'], $context);
    }
}

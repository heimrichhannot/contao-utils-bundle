<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Choice;

use Contao\System;

class DataContainerChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];

        try {
            foreach (System::getContainer()->get('contao.resource_finder')->findIn('dca')->name('tl_*.php') as $file) {
                /** @var \SplFileInfo $file */
                $name = $file->getBasename('.php');

                if (\in_array($name, $choices)) {
                    continue;
                }

                $choices[] = $name;
            }
        } catch (\InvalidArgumentException $e) {
        }

        sort($choices);

        return $choices;
    }
}

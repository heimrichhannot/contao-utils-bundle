<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Twig;

use Contao\Config;
use Contao\System;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DateExtension extends AbstractExtension
{
    /**
     * Get list of twig filters.
     *
     * @return array|\Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('localized_date', [$this, 'getLocalizedDate']),
        ];
    }

    public function getLocalizedDate($timestamp, string $format = null): string
    {
        if (null === $format) {
            $format = Config::get('dateFormat');
        }

        $dateUtil = System::getContainer()->get('huh.utils.date');

        $date = date($format, $timestamp);

        // translate months
        $date = $dateUtil->translateMonths($date);

        return $date;
    }
}

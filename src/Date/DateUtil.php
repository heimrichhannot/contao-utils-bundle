<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\UtilsBundle\Date;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;

class DateUtil
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    public function getTimePeriodInSeconds($timePeriod)
    {
        $timePeriod = deserialize($timePeriod, true);

        if (!isset($timePeriod['unit']) || !isset($timePeriod['value'])) {
            return null;
        }

        $factor = 1;

        switch ($timePeriod['unit']) {
            case 'm':
                $factor = 60;
                break;
            case 'h':
                $factor = 60 * 60;
                break;
            case 'd':
                $factor = 24 * 60 * 60;
                break;
        }

        return $timePeriod['value'] * $factor;
    }
}

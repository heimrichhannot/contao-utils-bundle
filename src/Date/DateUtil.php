<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
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

    /**
     * Format a php date formate pattern to an RFC3339 compliant format
     *
     * @param string $format The php date format (see: http://php.net/manual/de/function.date.php#refsect1-function.date-parameters)
     *
     * @return string The RFC3339 compliant format (see: http://userguide.icu-project.org/formatparse/datetime)
     */
    public function formatPhpDateToRFC3339(string $format): string
    {
        $mapping = [
            'd' => 'dd',  //Day of the month, 2 digits with leading zeros (01 to 31)
            'D' => 'E', // A textual representation of a day, three letters (Mon through Sun)
            'j' => 'd', // Day of the month without leading zeros (1 to 31)
            'l' => 'EEEE', // A full textual representation of the day of the week (Sunday through Saturday)
            'N' => 'd', // ISO-8601 numeric representation of the day of the week (added in PHP 5.1.0) (1 (for Monday) through 7 (for Sunday))
            'S' => '', // English ordinal suffix for the day of the month, 2 characters (st, nd, rd or th. Works well with j)
            'w' => 'e', // Numeric representation of the day of the week (0 (for Sunday) through 6 (for Saturday))
        ];

        return str_replace(array_keys($mapping), array_values($mapping), $format);
    }

    /**
     * Format a php date format string to javascript compatible date format string.
     *
     * @param string $php_format The date format (e.g. "d.m.y H:i")
     *
     * @return string The formatted js date string
     */
    public function formatPhpDateToJsDate($php_format)
    {
        $SYMBOLS_MATCHING = [
            // Day
            'd' => 'DD',
            'D' => 'D',
            'j' => 'd',
            'l' => 'DD',
            'N' => '',
            'S' => '',
            'w' => '',
            'z' => 'o',
            // Week
            'W' => '',
            // Month
            'F' => 'MM',
            'm' => 'MM',
            'M' => 'M',
            'n' => 'm',
            't' => '',
            // Year
            'L' => '',
            'o' => '',
            'Y' => 'YYYY',
            'y' => 'y',
            // Time
            'a' => '',
            'A' => '',
            'B' => '',
            'g' => '',
            'G' => '',
            'h' => '',
            'H' => 'HH',
            'i' => 'mm',
            's' => '',
            'u' => '',
        ];

        $replacement = '';
        $escaping    = false;

        for ($i = 0; $i < strlen($php_format); ++$i) {
            $char = $php_format[$i];
            if ('\\' === $char) {            // PHP date format escaping character
                ++$i;
                if ($escaping) {
                    $replacement .= $php_format[$i];
                } else {
                    $replacement .= '\'' . $php_format[$i];
                }
                $escaping = true;
            } else {
                if ($escaping) {
                    $replacement .= "'";
                    $escaping    = false;
                }
                if (isset($SYMBOLS_MATCHING[$char])) {
                    $replacement .= $SYMBOLS_MATCHING[$char];
                } else {
                    $replacement .= $char;
                }
            }
        }

        return $replacement;
    }
}

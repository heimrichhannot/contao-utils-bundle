<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests;

use Contao\Config;
use Contao\Date;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Date\DateUtil;

class DateUtilTest extends ContaoTestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $instance = new self();

        $this->assertInstanceOf(self::class, $instance);
    }

    /**
     * @dataProvider timeStampProvider
     */
    public function testGetTimeStamp($date, $expected, $replaceInsertTags = true, $timeZone = null, $compareFormat = null)
    {
        // Prevent "undefined index" errors
        $errorReporting = error_reporting();
        error_reporting($errorReporting & ~E_NOTICE);

        $container = $this->mockContainer();
        \System::setContainer($container);

        $instance = new DateUtil($this->mockContaoFramework());

        if (null !== $timeZone) {
            Config::set('timeZone', $timeZone);
        }

        if (null !== $compareFormat) {
            $this->assertSame(Date::parse($compareFormat, $expected), Date::parse($compareFormat, $instance->getTimeStamp($date, $replaceInsertTags, $timeZone)));

            return;
        }

        $this->assertSame($expected, $instance->getTimeStamp($date, $replaceInsertTags, $timeZone));
    }

    /**
     * @dataProvider phpDateRFC3339Provider
     */
    public function testTransformPhpDateFormatToRFC3339($format, $expected)
    {
        $instance = new DateUtil($this->mockContaoFramework());

        $this->assertSame($expected, $instance->transformPhpDateFormatToRFC3339($format));
    }

    /**
     * @dataProvider transformPhpDateFormatToRFC3339Provider
     */
    public function testTransformPhpDateFormatToRFC3339WithDate($format, $locale, \DateTime $date, $expected)
    {
        $timezone = $date->getTimezone()->getName();
        $calendar = \IntlDateFormatter::GREGORIAN;
        $pattern = null;
        $dateFormat = \IntlDateFormatter::MEDIUM; // default from Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToLocalizedStringTransformer
        $timeFormat = \IntlDateFormatter::SHORT; // default from Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToLocalizedStringTransformer

        $utils = new DateUtil($this->mockContaoFramework());
        $rfc3339Format = $utils->transformPhpDateFormatToRFC3339($format);

        $intlDateFormatter = new \IntlDateFormatter($locale, $dateFormat, $timeFormat, $timezone, $calendar, $pattern);
        $intlDateFormatter->setPattern($rfc3339Format);

        $this->assertSame($expected, $intlDateFormatter->format($date));
    }

    /**
     * The timestamp test data provider.
     */
    public function timeStampProvider()
    {
        return [
            [null, 0, true, 'GMT', null],
            [0, 0, true, 'GMT', null],
            [1511022657, 1511022657, true, null, null],
            [1511022657, 1511022657, true, 'GMT', null],
            ['1511022657', 1511022657, true, 'GMT', null],
            ['{{date::d.m.Y H:i}}', time(), true, 'GMT', 'd.m.Y H:i'],
            ['{{date::d.m.Y}}', time(), true, 'GMT', 'd.m.Y'],
            ['{{date::H:i}}', time(), true, 'GMT', 'H:i'],
            ['Mon, 12 Dec 2011 21:17:52 +0800', 1323695872, true, 'GMT', null],
            ['24.04.2018 17:45', 1524591900, true, null, null],
            ['24.04.2018 17:45', 1524617100, true, 'America/Los_Angeles', null],
            ['ABCDEF"!§v231', 0, true, null, null],
        ];
    }

    /**
     * The php to rfc3339 test data provider.
     */
    public function phpDateRFC3339Provider()
    {
        return [
            ['d', 'dd'],
            ['D', 'E'],
            ['j', 'd'],
            ['l', 'EEEE'],
            ['N', 'd'],
            ['S', ''],
            ['w', 'e'],
            ['z', 'D'],
            ['W', 'w'],
            ['F', 'MMMM'],
            ['m', 'MM'],
            ['M', 'MMM'],
            ['n', 'M'],
            ['t', ''],
            ['L', ''],
            ['o', 'Y'],
            ['Y', 'yyyy'],
            ['y', 'yy'],
            ['a', ''],
            ['A', 'a'],
            ['B', ''],
            ['g', 'h'],
            ['G', 'H'],
            ['h', 'hh'],
            ['H', 'HH'],
            ['i', 'mm'],
            ['s', 'ss'],
            ['u', ''],
            ['v', ''],
            ['e', 'VV'],
            ['I', ''],
            ['O', 'xx'],
            ['P', 'xxx'],
            ['T', ''],
            ['Z', ''],
            ['c', "yyyy-MM-dd'T'HH:mm:ssxxx"],
            ['r', ''],
            ['U', ''],
        ];
    }

    /**
     * The php to rfc3339 test data provider.
     */
    public function transformPhpDateFormatToRFC3339Provider()
    {
        $timeZone = 'Europe/Berlin';
        $date = new \DateTime();
        $date->setTimezone(new \DateTimeZone($timeZone));
        $date->setDate('2018', '04', '04');
        $date->setTime('16', '09', '02', '1234');

        return [
            ['d', 'en-EN', $date, '04'],
            ['D', 'en-EN', $date, 'Wed'],
            ['j', 'en-EN', $date, '4'],
            ['N', 'en-EN', $date, '4'],
            ['S', 'en-EN', $date, ''],
            ['w', 'en-EN', $date, '3'],
            ['z', 'en-EN', $date, '94'],
            ['W', 'en-EN', $date, '14'],
            ['F', 'en-EN', $date, 'April'],
            ['m', 'en-EN', $date, '04'],
            ['M', 'en-EN', $date, 'Apr'],
            ['M', 'en-EN', $date, 'Apr'],
            ['n', 'en-EN', $date, '4'],
            ['t', 'en-EN', $date, ''],
            ['L', 'en-EN', $date, ''],
            ['o', 'en-EN', $date, '2018'],
            ['o', 'en-EN', $date, '2018'],
            ['Y', 'en-EN', $date, '2018'],
            ['y', 'en-EN', $date, '18'],
            ['a', 'en-EN', $date, ''],
            ['A', 'en-EN', $date, 'PM'],
            ['B', 'en-EN', $date, ''],
            ['g', 'en-EN', $date, '4'],
            ['G', 'en-EN', $date, '16'],
            ['h', 'en-EN', $date, '04'],
            ['H', 'en-EN', $date, '16'],
            ['i', 'en-EN', $date, '09'],
            ['s', 'en-EN', $date, '02'],
            ['u', 'en-EN', $date, ''],
            ['v', 'en-EN', $date, ''],
            ['e', 'en-EN', $date, $timeZone],
            ['I', 'en-EN', $date, ''],
            ['O', 'en-EN', $date, '+0200'],
            ['P', 'en-EN', $date, '+02:00'],
            ['T', 'en-EN', $date, ''],
            ['Z', 'en-EN', $date, ''],
            ['c', 'en-EN', $date, '2018-04-04T16:09:02+02:00'],
            ['r', 'en-EN', $date, ''],
            ['U', 'en-EN', $date, ''],
            ['d.m.Y H:i', 'en-EN', $date, '04.04.2018 16:09'],
            ['d.m.Y', 'en-EN', $date, '04.04.2018'],
            ['H:i', 'en-EN', $date, '16:09'],
            ['Y-m-d H.i', 'en-EN', $date, '2018-04-04 16.09'],
            ['Y-m-d', 'en-EN', $date, '2018-04-04'],
            ['H.i', 'en-EN', $date, '16.09'],
        ];
    }

    public function testGetTimePeriodInSeconds()
    {
        if (!\function_exists('deserialize')) {
            include_once __DIR__.'/../../vendor/contao/core-bundle/src/Resources/contao/helper/functions.php';
        }
        $date = new DateUtil($this->mockContaoFramework());

        $timePeriod = serialize(['unit' => 'h', 'value' => 12]);
        $result = $date->getTimePeriodInSeconds($timePeriod);
        $this->assertSame(43200, $result);

        $timePeriod = serialize(['unit' => 'm', 'value' => 12]);
        $result = $date->getTimePeriodInSeconds($timePeriod);
        $this->assertSame(720, $result);

        $timePeriod = serialize(['unit' => 'd', 'value' => 12]);
        $result = $date->getTimePeriodInSeconds($timePeriod);
        $this->assertSame(1036800, $result);

        $timePeriod = serialize(['units' => 'h', 'value' => 12]);
        $result = $date->getTimePeriodInSeconds($timePeriod);
        $this->assertNull($result);
    }

    public function testFormatPhpDateToJsDate()
    {
        $date = new DateUtil($this->mockContaoFramework());
        $result = $date->formatPhpDateToJsDate('d.m.y H:i');
        $this->assertSame('DD.MM.y HH:mm', $result);

        $result = $date->formatPhpDateToJsDate('d.m.Y \m \i\s\ \m\o\n\t\h');
        $this->assertSame("DD.MM.YYYY 'm' 'is month", $result);
    }
}

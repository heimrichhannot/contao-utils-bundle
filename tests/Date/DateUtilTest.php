<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\UtilsBundle\Tests;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Date\DateUtil;

class DateUtilTest extends ContaoTestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $instance = new DateUtilTest();

        $this->assertInstanceOf(DateUtilTest::class, $instance);
    }

    /**
     * @dataProvider phpDateRFC3339Provider
     */
    public function testFormatPhpDateToRFC3339($format, $expected)
    {
        $instance = new DateUtil($this->mockContaoFramework());

        $this->assertEquals($expected, $instance->formatPhpDateToRFC3339($format));
    }

    /**
     * @dataProvider transformPhpDateRFC3339Provider
     */
    public function testTransformPhpDateToRFC3339($format, $locale, $date, $expected)
    {
        $timezone   = 'UTC';
        $calendar   = \IntlDateFormatter::GREGORIAN;
        $pattern    = null;
        $dateFormat = \IntlDateFormatter::MEDIUM; // default from Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToLocalizedStringTransformer
        $timeFormat = \IntlDateFormatter::SHORT; // default from Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToLocalizedStringTransformer

        $utils         = new DateUtil($this->mockContaoFramework());
        $rfc3339Format = $utils->formatPhpDateToRFC3339($format);

        $intlDateFormatter = new \IntlDateFormatter($locale, $dateFormat, $timeFormat, $timezone, $calendar, $pattern);
        $intlDateFormatter->setPattern($rfc3339Format);

        $this->assertEquals($expected, $intlDateFormatter->format($date));
    }

    /**
     * The php to rfc3339 test data provider
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
        ];
    }

    /**
     * The php to rfc3339 test data provider
     */
    public function transformPhpDateRFC3339Provider()
    {
        $date = new \DateTime();
        $date->setDate('2018', '02', '04');
        $date->setTime('16', '33', '12', '0');

        return [
            ['d', 'en-EN', $date, '04'],
            ['D', 'en-EN', $date, 'Sun'],
            ['j', 'en-EN', $date, '4'],
            ['N', 'en-EN', $date, '4'],
            ['S', 'en-EN', $date, ''],
            ['w', 'en-EN', $date, '7'],
        ];
    }

}
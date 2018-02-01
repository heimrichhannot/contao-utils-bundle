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
    public function testTransformPhpDateFormatToRFC3339($format, $expected)
    {
        $instance = new DateUtil($this->mockContaoFramework());

        $this->assertEquals($expected, $instance->transformPhpDateFormatToRFC3339($format));
    }

    /**
     * @dataProvider transformPhpDateFormatToRFC3339Provider
     */
    public function testTransformPhpDateFormatToRFC3339WithDate($format, $locale, \DateTime $date, $expected)
    {
        $timezone   = $date->getTimezone()->getName();
        $calendar   = \IntlDateFormatter::GREGORIAN;
        $pattern    = null;
        $dateFormat = \IntlDateFormatter::MEDIUM; // default from Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToLocalizedStringTransformer
        $timeFormat = \IntlDateFormatter::SHORT; // default from Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToLocalizedStringTransformer

        $utils         = new DateUtil($this->mockContaoFramework());
        $rfc3339Format = $utils->transformPhpDateFormatToRFC3339($format);

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
        ];
    }

    /**
     * The php to rfc3339 test data provider
     */
    public function transformPhpDateFormatToRFC3339Provider()
    {
        $date = new \DateTime();
        $date->setTimezone(new \DateTimeZone('UTC'));
        $date->setDate('2018', '02', '04');
        $date->setTime('16', '09', '02', '1234');

        return [
            ['d', 'en-EN', $date, '04'],
            ['D', 'en-EN', $date, 'Sun'],
            ['j', 'en-EN', $date, '4'],
            ['N', 'en-EN', $date, '4'],
            ['S', 'en-EN', $date, ''],
            ['w', 'en-EN', $date, '7'],
            ['z', 'en-EN', $date, '35'],
            ['W', 'en-EN', $date, '5'],
            ['F', 'en-EN', $date, 'February'],
            ['m', 'en-EN', $date, '02'],
            ['M', 'en-EN', $date, 'Feb'],
            ['M', 'en-EN', $date, 'Feb'],
            ['n', 'en-EN', $date, '2'],
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
        ];
    }

}
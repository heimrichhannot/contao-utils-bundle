<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Util\Locale;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Util\LocaleUtil;

class LocaleUtilTest extends ContaoTestCase
{
    public function getTestInstance(array $parameters = [])
    {
        return new LocaleUtil();
    }

    /**
     * @dataProvider providerEncoreLineBreaks
     */
    public function testEnsureLineBreaks(string $text, string $expected, string $language)
    {
        $instance = $this->getTestInstance();

        $this->assertSame($expected, $instance->ensureLineBreaks($text, $language));
    }

    public function providerEncoreLineBreaks()
    {
        return [
            ['Hello World!', 'Hello World!', 'en'],
            ['Hello World!', 'Hello World!', 'cs'],
            ['Hello World! A my case!', 'Hello World! A&nbsp;my case!', 'cs'],
            ["Hello World! A\n my case!", 'Hello World! A&nbsp; my case!', 'cs'],
            ['Jsem z Německa.', 'Jsem z&nbsp;Německa.', 'cs'],
            ["Jsem z\n Německa.", 'Jsem z&nbsp; Německa.', 'cs'],
        ];
    }
}

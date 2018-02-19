<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\String;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;

class StringUtil
{
    const CAPITAL_LETTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const CAPITAL_LETTERS_NONAMBIGUOUS = 'ABCDEFGHJKLMNPQRSTUVWX';
    const SMALL_LETTERS = 'abcdefghijklmnopqrstuvwxyz';
    const SMALL_LETTERS_NONAMBIGUOUS = 'abcdefghjkmnpqrstuvwx';
    const NUMBERS = '0123456789';
    const NUMBERS_NONAMBIGUOUS = '23456789';

    /** @var ContaoFrameworkInterface */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Check for the occurrence at the start of the string.
     *
     * @param $haystack string The string to search in
     * @param $needle   string The needle
     *
     * @return bool
     */
    public function startsWith($haystack, $needle)
    {
        return '' === $needle || false !== strrpos($haystack, $needle, -strlen($haystack));
    }

    /**
     * Check for the occurrence at the end of the string.
     *
     * @param string $haystack The string to search in
     * @param string $needle   The needle
     *
     * @return bool
     */
    public static function endsWith($haystack, $needle)
    {
        // search forward starting from end minus needle length characters
        return '' === $needle || (($temp = strlen($haystack) - strlen($needle)) >= 0 && false !== strpos($haystack, $needle, $temp));
    }

    public function camelCaseToDashed($value)
    {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $value));
    }

    /**
     * @codeCoverageIgnore
     *
     * @param bool $includeAmbiguousChars
     *
     * @return mixed
     */
    public function randomChar(bool $includeAmbiguousChars = false)
    {
        if ($includeAmbiguousChars) {
            $chars = static::CAPITAL_LETTERS.static::SMALL_LETTERS.static::NUMBERS;
        } else {
            $chars = static::CAPITAL_LETTERS_NONAMBIGUOUS.static::SMALL_LETTERS_NONAMBIGUOUS.static::NUMBERS_NONAMBIGUOUS;
        }

        return $chars[rand(0, $includeAmbiguousChars ? 61 : 50)];
    }

    /**
     * @codeCoverageIgnore
     *
     * @param bool $includeAmbiguousChars
     *
     * @return mixed
     */
    public function randomLetter(bool $includeAmbiguousChars = false)
    {
        if ($includeAmbiguousChars) {
            $chars = static::CAPITAL_LETTERS.static::SMALL_LETTERS;
        } else {
            $chars = static::CAPITAL_LETTERS_NONAMBIGUOUS.static::SMALL_LETTERS_NONAMBIGUOUS;
        }

        return $chars[rand(0, $includeAmbiguousChars ? 51 : 42)];
    }

    /**
     * @codeCoverageIgnore
     *
     * @param bool $includeAmbiguousChars
     *
     * @return mixed
     */
    public function randomNumber(bool $includeAmbiguousChars = false)
    {
        if ($includeAmbiguousChars) {
            $chars = static::NUMBERS;
        } else {
            $chars = static::NUMBERS_NONAMBIGUOUS;
        }

        return $chars[rand(0, $includeAmbiguousChars ? 9 : 7)];
    }

    /**
     * @codeCoverageIgnore
     *
     * @param string $charList
     *
     * @return mixed
     */
    public function random(string $charList)
    {
        return $charList[rand(0, strlen($charList) - 1)];
    }
}

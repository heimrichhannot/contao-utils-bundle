<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Security;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use Hackzilla\PasswordGenerator\Generator\ComputerPasswordGenerator;
use HeimrichHannot\UtilsBundle\String\StringUtil;

class CodeUtil
{
    const CAPITAL_LETTERS = 'capitalLetters';
    const SMALL_LETTERS = 'smallLetters';
    const NUMBERS = 'numbers';
    const SPECIAL_CHARS = 'specialChars';

    const DEFAULT_ALPHABETS = [
        self::CAPITAL_LETTERS,
        self::SMALL_LETTERS,
        self::NUMBERS,
    ];

    const DEFAULT_RULES = [
        self::CAPITAL_LETTERS,
        self::SMALL_LETTERS,
        self::NUMBERS,
    ];

    const DEFAULT_ALLOWED_SPECIAL_CHARS = '[=<>()#/]';
    /** @var ContaoFrameworkInterface */
    protected $framework;

    protected static $blnPreventAmbiguous = true;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Generates a code by certain criteria.
     *
     * @return mixed
     */
    public static function generate(
        int $length = 8,
        bool $preventAmbiguous = true,
        array $alphabets = null,
        array $rules = null,
        string $allowedSpecialChars = null
    ) {
        $stringUtil = System::getContainer()->get('huh.utils.string');

        $alphabets = \is_array($alphabets) ? $alphabets : static::DEFAULT_ALPHABETS;
        $rules = \is_array($rules) ? $rules : static::DEFAULT_RULES;
        $allowedSpecialChars = null !== $allowedSpecialChars ? $allowedSpecialChars : static::DEFAULT_ALLOWED_SPECIAL_CHARS;

        $generator = new ComputerPasswordGenerator();
        $generator
            ->setLength($length)
            ->setNumbers(\in_array(static::NUMBERS, $alphabets, true) && \in_array(static::NUMBERS, $rules, true))
            ->setUppercase(\in_array(static::CAPITAL_LETTERS, $alphabets, true) && \in_array(static::CAPITAL_LETTERS, $rules, true))
            ->setAvoidSimilar($preventAmbiguous)
            ->setSymbols(\in_array(static::SPECIAL_CHARS, $alphabets, true) && \in_array(static::SPECIAL_CHARS, $rules, true));

        $code = $generator->generatePassword();

        // replace remaining ambiguous characters
        if ($preventAmbiguous) {
            $charReplacements = ['y', 'Y', 'z', 'Z', 'o', 'O', 'i', 'I', 'l'];

            foreach ($charReplacements as $char) {
                $code = str_replace($char, $stringUtil->randomChar(!$preventAmbiguous), $code);
            }
        }

        // apply allowed alphabets
        $forbiddenPattern = '';
        $allowedChars = '';

        if (!\in_array(static::CAPITAL_LETTERS, $alphabets)) {
            $forbiddenPattern .= 'A-Z';
        } else {
            $allowedChars .= ($preventAmbiguous ? StringUtil::CAPITAL_LETTERS_NONAMBIGUOUS : StringUtil::CAPITAL_LETTERS);
        }

        if (!\in_array(static::SMALL_LETTERS, $alphabets)) {
            $forbiddenPattern .= 'a-z';
        } else {
            $allowedChars .= ($preventAmbiguous ? StringUtil::SMALL_LETTERS_NONAMBIGUOUS : StringUtil::SMALL_LETTERS);
        }

        if (!\in_array(static::NUMBERS, $alphabets)) {
            $forbiddenPattern .= '0-9';
        } else {
            $allowedChars .= ($preventAmbiguous ? StringUtil::NUMBERS_NONAMBIGUOUS : StringUtil::NUMBERS);
        }

        if ('' === $allowedChars) {
            return $code;
        }

        if ($forbiddenPattern) {
            $code = preg_replace_callback('@['.$forbiddenPattern.']{1}@', function () use ($allowedChars, $stringUtil) {
                return $stringUtil->random($allowedChars);
            }, $code);
        }

        // special chars
        if (!\in_array(static::SPECIAL_CHARS, $alphabets)) {
            $code = preg_replace_callback('@[^'.$allowedChars.']{1}@', function () use ($allowedChars, $stringUtil) {
                return $stringUtil->random($allowedChars);
            }, $code);
        } else {
            $code = preg_replace_callback('@[^'.$allowedChars.']{1}@', function () use ($allowedSpecialChars, $stringUtil) {
                return $stringUtil->random($allowedSpecialChars);
            }, $code);
        }

        return $code;
    }
}

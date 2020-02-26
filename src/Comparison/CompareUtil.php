<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Comparison;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;

class CompareUtil
{
    const PHP_OPERATOR_EQUAL = 'equal';
    const PHP_OPERATOR_UNEQUAL = 'unequal';
    const PHP_OPERATOR_LIKE = 'like';
    const PHP_OPERATOR_UNLIKE = 'unlike';
    const PHP_OPERATOR_IN_ARRAY = 'inarray';
    const PHP_OPERATOR_NOT_IN_ARRAY = 'notinarray';
    const PHP_OPERATOR_LOWER = 'lower';
    const PHP_OPERATOR_LOWER_EQUAL = 'lowerequal';
    const PHP_OPERATOR_GREATER = 'greater';
    const PHP_OPERATOR_GREATER_EQUAL = 'greaterequal';
    const PHP_OPERATOR_IS_NULL = 'isnull';
    const PHP_OPERATOR_IS_NOT_NULL = 'isnotnull';

    const PHP_OPERATORS
        = [
            self::PHP_OPERATOR_EQUAL,
            self::PHP_OPERATOR_UNEQUAL,
            self::PHP_OPERATOR_LIKE,
            self::PHP_OPERATOR_UNLIKE,
            self::PHP_OPERATOR_IN_ARRAY,
            self::PHP_OPERATOR_NOT_IN_ARRAY,
            self::PHP_OPERATOR_LOWER,
            self::PHP_OPERATOR_LOWER_EQUAL,
            self::PHP_OPERATOR_GREATER,
            self::PHP_OPERATOR_GREATER_EQUAL,
            self::PHP_OPERATOR_IS_NULL,
            self::PHP_OPERATOR_IS_NOT_NULL,
        ];

    const PHP_SINGLE_VALUE_OPERATORS = [
        self::PHP_OPERATOR_IS_NULL,
        self::PHP_OPERATOR_IS_NOT_NULL,
    ];

    /** @var ContaoFrameworkInterface */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * @param $value1
     * @param $value2
     */
    public function compareValue(string $operator, $value1, $value2 = null): bool
    {
        if (\in_array($operator, self::PHP_SINGLE_VALUE_OPERATORS)) {
            return $this->compareSingleValue($value1, $operator);
        }

        if (!$value1 || !$value2) {
            return false;
        }

        switch ($operator) {
            case self::PHP_OPERATOR_EQUAL:
                return $value1 == $value2;

                break;

            case self::PHP_OPERATOR_UNEQUAL:
                return $value1 != $value2;

                break;

            case self::PHP_OPERATOR_LIKE:
                return false !== strpos($value1, $value2);

                break;

            case self::PHP_OPERATOR_UNLIKE:
                return false === strpos($value1, $value2);

                break;

            case self::PHP_OPERATOR_IN_ARRAY:
                return \in_array($value2, $value1);

                break;

            case self::PHP_OPERATOR_NOT_IN_ARRAY:
                return !\in_array($value2, $value1);

                break;

            case self::PHP_OPERATOR_LOWER:
                return $value1 < $value2;

                break;

            case self::PHP_OPERATOR_LOWER_EQUAL:
                return $value1 <= $value2;

                break;

            case self::PHP_OPERATOR_GREATER:
                return $value1 > $value2;

                break;

            case self::PHP_OPERATOR_GREATER_EQUAL:
                return $value1 >= $value2;
        }
    }

    /**
     * @param $value
     */
    public function compareSingleValue($value, string $operator): bool
    {
        switch ($operator) {
            case self::PHP_OPERATOR_IS_NULL:
                return null === $value;

                break;

            case self::PHP_OPERATOR_IS_NOT_NULL:
                return null !== $value;
        }
    }
}

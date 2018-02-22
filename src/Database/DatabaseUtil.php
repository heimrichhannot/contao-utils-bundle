<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Database;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\Model;
use Contao\System;
use Doctrine\DBAL\Query\QueryBuilder;

class DatabaseUtil
{
    const SQL_CONDITION_OR = 'OR';
    const SQL_CONDITION_AND = 'AND';

    const OPERATOR_LIKE = 'like';
    const OPERATOR_UNLIKE = 'unlike';
    const OPERATOR_EQUAL = 'equal';
    const OPERATOR_UNEQUAL = 'unequal';
    const OPERATOR_LOWER = 'lower';
    const OPERATOR_LOWER_EQUAL = 'lowerequal';
    const OPERATOR_GREATER = 'greater';
    const OPERATOR_GREATER_EQUAL = 'greaterequal';
    const OPERATOR_IN = 'in';
    const OPERATOR_NOT_IN = 'notin';
    const OPERATOR_IS_NULL = 'isnull';
    const OPERATOR_IS_NOT_NULL = 'isnotnull';
    const OPERATOR_REGEXP = 'regexp';

    const ON_DUPLICATE_KEY_IGNORE = 'IGNORE';
    const ON_DUPLICATE_KEY_UPDATE = 'UPDATE';

    const OPERATORS = [
        self::OPERATOR_LIKE,
        self::OPERATOR_UNLIKE,
        self::OPERATOR_EQUAL,
        self::OPERATOR_UNEQUAL,
        self::OPERATOR_LOWER,
        self::OPERATOR_LOWER_EQUAL,
        self::OPERATOR_GREATER,
        self::OPERATOR_GREATER_EQUAL,
        self::OPERATOR_IN,
        self::OPERATOR_NOT_IN,
        self::OPERATOR_IS_NULL,
        self::OPERATOR_IS_NOT_NULL,
        self::OPERATOR_REGEXP,
    ];

    /**
     * Maps operators of this class to its corresponding Doctrine ExpressionBuilder method.
     */
    const OPERATOR_MAPPING = [
        self::OPERATOR_LIKE => 'like',
        self::OPERATOR_UNLIKE => 'notLike',
        self::OPERATOR_EQUAL => 'eq',
        self::OPERATOR_UNEQUAL => 'neq',
        self::OPERATOR_LOWER => 'lt',
        self::OPERATOR_LOWER_EQUAL => 'lte',
        self::OPERATOR_GREATER => 'gt',
        self::OPERATOR_GREATER_EQUAL => 'gte',
        self::OPERATOR_IN => 'in',
        self::OPERATOR_NOT_IN => 'notIn',
        self::OPERATOR_IS_NULL => 'isNull',
        self::OPERATOR_IS_NOT_NULL => 'isNotNull',
    ];

    /** @var ContaoFrameworkInterface */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Process a query in pieces, run callback within each cycle.
     *
     * @param string   $countQuery The query that count the total rows, must contain "Select COUNT(*) as total"
     * @param string   $query      The query, with the rows that should be iterated over
     * @param callable $callback   A callback that should be triggered after each cycle, contains $arrRows of current cycle
     * @param string   $key        The key of the value that should be set as key identifier for the returned result array entries
     * @param int      $bulkSize   The bulk size
     *
     * @return bool|int False if nothing to do, otherwise return the total number of processes entities
     */
    public function processInPieces(string $countQuery, string $query, $callback = null, string $key = null, int $bulkSize = 5000)
    {
        /** @var Database $database */
        $database = $this->framework->createInstance(Database::class);
        $total = $database->execute($countQuery);

        if ($total->total < 1) {
            return false;
        }

        $bulkSize = (int) $bulkSize;
        $totalCount = $total->total;
        $cycles = $totalCount / $bulkSize;

        for ($i = 0; $i <= $cycles; ++$i) {
            $result = $database->prepare($query)->limit($bulkSize, $i * $bulkSize)->execute();

            if ($result->numRows < 1) {
                return false;
            }

            if (is_callable($callback)) {
                $return = [];

                while (false !== ($row = $result->fetchAssoc())) {
                    if ($key) {
                        if (isset($row[$key])) {
                            $return[$row[$key]] = $row;
                        }
                        continue;
                    }

                    $return[] = $row;
                }

                call_user_func_array($callback, [$return]);
            }
        }

        return $totalCount;
    }

    /**
     * Bulk insert SQL of given data.
     *
     * @param string   $table          The database table, where new items should be stored inside
     * @param array    $data           An array of values associated to its field
     * @param array    $fixedValues    A array of fixed values associated to its field that should be set for each row as fixed values
     * @param mixed    $onDuplicateKey null = Throw error on duplicates, self::ON_DUPLICATE_KEY_IGNORE = ignore error duplicates (skip this entries),
     *                                 self::ON_DUPLICATE_KEY_UPDATE = update existing entries
     * @param callable $callback       A callback that should be triggered after each cycle, contains $arrValues of current cycle
     * @param callable $itemCallback   A callback to change the insert values for each items, contains $arrValues as first argument, $arrFields as
     *                                 second, $arrOriginal as third, expects an array as return value with same order as $arrFields, if no array is
     *                                 returned, insert of the row will be skipped item insert
     * @param int      $bulkSize       The bulk size
     * @param string   $pk             The primary key of the current table (default: id)
     */
    public function doBulkInsert(
        string $table,
        array $data = [],
        array $fixedValues = [],
        $onDuplicateKey = null,
        $callback = null,
        $itemCallback = null,
        int $bulkSize = 100,
        string $pk = 'id'
    ) {
        /** @var Database $database */
        $database = $this->framework->createInstance(Database::class);

        if (!$database->tableExists($table) || empty($data)) {
            return null;
        }

        $fields = $database->getFieldNames($table, true);
        System::getContainer()->get('huh.utils.array')->removeValue($pk, $fields); // unset id
        $fields = array_values($fields);

        $bulkSize = (int) $bulkSize;

        $query = '';
        $duplicateKey = '';
        $startQuery = sprintf('INSERT %s INTO %s (%s) VALUES ', self::ON_DUPLICATE_KEY_IGNORE == $onDuplicateKey ? 'IGNORE' : '', $table, implode(',', $fields));

        if (self::ON_DUPLICATE_KEY_UPDATE == $onDuplicateKey) {
            $duplicateKey = ' ON DUPLICATE KEY UPDATE '.implode(',', array_map(function ($val) {
                // escape double quotes
                return $val.' = VALUES('.$val.')';
            }, $fields));
        }

        $i = 0;

        $columnWildcards = array_map(function ($val) {
            return '?';
        }, $fields);

        foreach ($data as $key => $varData) {
            if (0 == $i) {
                $values = [];
                $return = [];
                $query = $startQuery;
            }

            $columns = $columnWildcards;

            if ($varData instanceof Model) {
                $varData = $varData->row();
            }

            foreach ($fields as $n => $strField) {
                if (!isset($varData[$strField])) {
                    continue;
                }
                $varValue = $varData[$strField] ?: 'DEFAULT';

                if (in_array($strField, array_keys($fixedValues), true)) {
                    $varValue = $fixedValues[$strField];
                }

                // replace SQL Keyword DEFAULT within wildcards ?
                if ('DEFAULT' == $varValue) {
                    $columns[$n] = 'DEFAULT';
                    continue;
                }

                $return[$i][$strField] = $varValue;
            }

            // manipulate the item
            if (is_callable($itemCallback)) {
                if (!isset($return[$i])) {
                    continue;
                }
                $varCallback = call_user_func_array($itemCallback, [$return[$i], $fields, $varData]);

                if (!is_array($varCallback)) {
                    continue;
                }

                foreach ($fields as $n => $strField) {
                    $varValue = $varCallback[$strField] ?: 'DEFAULT';

                    // replace SQL Keyword DEFAULT within wildcards ?
                    if ('DEFAULT' == $varValue) {
                        $columns[$n] = 'DEFAULT';
                        continue;
                    }

                    $columns[$n] = '?';
                    $return[$i][$strField] = $varValue;
                }
            }

            // add values to insert array
            $values = array_merge($values, array_values($return[$i]));

            $query .= '('.implode(',', $columns).'),';

            ++$i;

            if ($bulkSize == $i) {
                $query = rtrim($query, ',');

                if (self::ON_DUPLICATE_KEY_UPDATE == $onDuplicateKey) {
                    $query .= $duplicateKey;
                }

                $database->prepare($query)->execute($values);

                if (is_callable($callback)) {
                    call_user_func_array($callback, [$return]);
                }

                $query = '';

                $i = 0;
            }
        }

        // remaining elements < $intBulkSize
        if ($query) {
            $query = rtrim($query, ',');

            if (self::ON_DUPLICATE_KEY_UPDATE == $onDuplicateKey) {
                $query .= $duplicateKey;
            }

            $database->prepare($query)->execute($values);

            if (is_callable($callback)) {
                call_user_func_array($callback, [$return]);
            }
        }
    }

    /**
     * Create a where condition for a field that contains a serialized blob.
     *
     * @param string $field      The field the condition should be checked against accordances
     * @param array  $values     The values array to check the field against
     * @param string $connective SQL_CONDITION_OR | SQL_CONDITION_AND
     *
     * @return array
     */
    public function createWhereForSerializedBlob(string $field, array $values, string $connective = self::SQL_CONDITION_OR)
    {
        $where = null;
        $returnValues = [];

        if (!in_array($connective, [self::SQL_CONDITION_OR, self::SQL_CONDITION_AND], true)) {
            throw new \Exception('Unknown sql junctor');
        }

        foreach ($values as $val) {
            if (null !== $where) {
                $where .= " $connective ";
            }

            $where .= self::SQL_CONDITION_AND == $connective ? '(' : '';

            $where .= "$field REGEXP (?)";

            $where .= self::SQL_CONDITION_AND == $connective ? ')' : '';

            $returnValues[] = "':\"$val\"'";
        }

        return ["($where)", $returnValues];
    }

    /**
     * Transforms verbose operators to valid MySQL operators (aka junctors).
     * Supports: like, unlike, equal, unequal, lower, greater, lowerequal, greaterequal, in, notin.
     *
     * @param string $verboseOperator
     *
     * @return string|bool The transformed operator or false if not supported
     */
    public function transformVerboseOperator(string $verboseOperator)
    {
        switch ($verboseOperator) {
            case static::OPERATOR_LIKE:
                return 'LIKE';
                break;
            case static::OPERATOR_UNLIKE:
                return 'NOT LIKE';
                break;
            case static::OPERATOR_EQUAL:
                return '=';
                break;
            case static::OPERATOR_UNEQUAL:
                return '!=';
                break;
            case static::OPERATOR_LOWER:
                return '<';
                break;
            case static::OPERATOR_GREATER:
                return '>';
                break;
            case static::OPERATOR_LOWER_EQUAL:
                return '<=';
                break;
            case static::OPERATOR_GREATER_EQUAL:
                return '>=';
                break;
            case static::OPERATOR_IN:
                return 'IN';
                break;
            case static::OPERATOR_NOT_IN:
                return 'NOT IN';
            case static::OPERATOR_IS_NULL:
                return 'NOT IN';
            case static::OPERATOR_IS_NOT_NULL:
                return 'IS NOT NULL';
                break;
        }

        return false;
    }

    /**
     * Computes a MySQL condition appropriate for the given operator.
     *
     * @param string $field
     * @param string $operator
     * @param        $value
     *
     * @return array Returns array($strQuery, $arrValues)
     */
    public function computeCondition(string $field, string $operator, $value, string $table = null)
    {
        $operator = trim(strtolower($operator));
        $values = [];
        $pattern = '?';
        $addQuotes = false;

        if ($table) {
            Controller::loadDataContainer($table);

            $dca = &$GLOBALS['TL_DCA'][$table]['fields'][$field];

            if (isset($dca['sql']) && false !== stripos($dca['sql'], 'blob')) {
                $addQuotes = true;
            }
        }

        switch ($operator) {
            case static::OPERATOR_UNLIKE:
                if (is_array($value)) {
                    foreach ($value as $val) {
                        $values[] = '%'.($addQuotes ? '"'.$val.'"' : $val).'%';
                    }
                    break;
                }
                $values[] = '%'.($addQuotes ? '"'.$value.'"' : $value).'%';
                break;
            case static::OPERATOR_EQUAL:
                $values[] = $value;
                break;
            case static::OPERATOR_UNEQUAL:
            case '<>':
                $values[] = $value;
                break;
            case static::OPERATOR_LOWER:
                $pattern = 'CAST(? AS DECIMAL)';
                $values[] = $value;
                break;
            case static::OPERATOR_GREATER:
                $pattern = 'CAST(? AS DECIMAL)';
                $values[] = $value;
                break;
            case static::OPERATOR_LOWER_EQUAL:
                $pattern = 'CAST(? AS DECIMAL)';
                $values[] = $value;
                break;
            case static::OPERATOR_GREATER_EQUAL:
                $pattern = 'CAST(? AS DECIMAL)';
                $values[] = $value;
                break;
            case static::OPERATOR_IN:
                $pattern = '('.implode(',', array_map(function ($value) {
                    return '\''.$value.'\'';
                }, explode(',', $value))).')';
                break;
            case static::OPERATOR_NOT_IN:
                $pattern = '('.implode(',', array_map(function ($value) {
                    return '\''.$value.'\'';
                }, explode(',', $value))).')';
                break;
            case static::OPERATOR_IS_NULL:
                $pattern = '';
                break;
            case static::OPERATOR_IS_NOT_NULL:
                $pattern = '';
                break;
            default:
                if (is_array($value)) {
                    foreach ($value as $val) {
                        $values[] = '%'.($addQuotes ? '"'.$val.'"' : $val).'%';
                    }
                    break;
                }
                $values[] = '%'.($addQuotes ? '"'.$value.'"' : $value).'%';
                break;
        }

        $operator = $this->transformVerboseOperator($operator);

        return ["$field $operator $pattern", $values];
    }

    public function composeWhereForQueryBuilder(QueryBuilder $queryBuilder, string $field, string $operator, array $dca, $value = null)
    {
        $wildcard = ':'.$field;
        $where = '';

        switch ($operator) {
            case self::OPERATOR_LIKE:
                $where = $queryBuilder->expr()->like($field, $wildcard);
                $queryBuilder->setParameter($wildcard, '%'.$value.'%');
                break;
            case self::OPERATOR_UNLIKE:
                $where = $queryBuilder->expr()->notLike($field, $wildcard);
                $queryBuilder->setParameter($wildcard, '%'.$value.'%');
                break;
            case self::OPERATOR_EQUAL:
                $where = $queryBuilder->expr()->eq($field, $wildcard);
                $queryBuilder->setParameter($wildcard, $value);
                break;
            case self::OPERATOR_UNEQUAL:
                $where = $queryBuilder->expr()->neq($field, $wildcard);
                $queryBuilder->setParameter($wildcard, $value);
                break;
            case self::OPERATOR_LOWER:
                $where = $queryBuilder->expr()->lt($field, $wildcard);
                $queryBuilder->setParameter($wildcard, $value);
                break;
            case self::OPERATOR_LOWER_EQUAL:
                $where = $queryBuilder->expr()->lte($field, $wildcard);
                $queryBuilder->setParameter($wildcard, $value);
                break;
            case self::OPERATOR_GREATER:
                $where = $queryBuilder->expr()->gt($field, $wildcard);
                $queryBuilder->setParameter($wildcard, $value);
                break;
            case self::OPERATOR_GREATER_EQUAL:
                $where = $queryBuilder->expr()->gte($field, $wildcard);
                $queryBuilder->setParameter($wildcard, $value);
                break;
            case self::OPERATOR_IN:
                $where = $queryBuilder->expr()->in($field, $wildcard);
                // always handle array items as strings
                $queryBuilder->setParameter($wildcard, $value, \PDO::PARAM_STR);
                break;
            case self::OPERATOR_NOT_IN:
                $where = $queryBuilder->expr()->notIn($field, $wildcard);
                // always handle array items as strings
                $queryBuilder->setParameter($wildcard, $value, \PDO::PARAM_STR);
                break;
            case self::OPERATOR_IS_NULL:
                $where = $queryBuilder->expr()->isNull($field);
                break;
            case self::OPERATOR_IS_NOT_NULL:
                $where = $queryBuilder->expr()->isNotNull($field);
                break;
            case self::OPERATOR_REGEXP:
                $where = $field.' REGEXP '.$wildcard;

                if (isset($dca['eval']['multiple']) && $dca['eval']['multiple']) {
                    // match a serialized blob
                    if (is_array($value)) {
                        // build a regexp alternative, e.g. (:"1";|:"2";)
                        $queryBuilder->setParameter($wildcard, '('.implode('|', array_map(function ($val) {
                            return ':"'.$val.'";';
                        }, $value)).')');
                    } else {
                        $queryBuilder->setParameter($wildcard, ':"'.$value.'";');
                    }
                } else {
                    // TODO: this makes no sense, yet
                    $queryBuilder->setParameter($wildcard, $value);
                }

                break;
        }

        return $where;
    }
}

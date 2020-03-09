<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
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
    const OPERATOR_NOT_REGEXP = 'notregexp';
    const OPERATOR_IS_EMPTY = 'isempty';
    const OPERATOR_IS_NOT_EMPTY = 'isnotempty';

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
        self::OPERATOR_IS_EMPTY,
        self::OPERATOR_IS_NOT_EMPTY,
        self::OPERATOR_REGEXP,
        self::OPERATOR_NOT_REGEXP,
    ];

    const NEGATIVE_OPERATORS = [
        self::OPERATOR_UNLIKE,
        self::OPERATOR_UNEQUAL,
        self::OPERATOR_NOT_IN,
        self::OPERATOR_IS_NULL,
        self::OPERATOR_IS_EMPTY,
        self::OPERATOR_NOT_REGEXP,
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

            if (\is_callable($callback)) {
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

                \call_user_func_array($callback, [$return]);
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
            $duplicateKey = ' ON DUPLICATE KEY UPDATE '.implode(
                    ',',
                    array_map(
                        function ($val) {
                            // escape double quotes
                            return $val.' = VALUES('.$val.')';
                        },
                        $fields
                    )
                );
        }

        $i = 0;

        $columnWildcards = array_map(
            function ($val) {
                return '?';
            },
            $fields
        );

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
                $varValue = isset($varData[$strField]) ? $varData[$strField] : 'DEFAULT';

                if (\in_array($strField, array_keys($fixedValues))) {
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
            if (\is_callable($itemCallback)) {
                if (!isset($return[$i])) {
                    continue;
                }
                $varCallback = \call_user_func_array($itemCallback, [$return[$i], $fields, $varData]);

                if (!\is_array($varCallback)) {
                    continue;
                }

                foreach ($fields as $n => $strField) {
                    $varValue = isset($varCallback[$strField]) ? $varCallback[$strField] : 'DEFAULT';

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

                if (\is_callable($callback)) {
                    \call_user_func_array($callback, [$return]);
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

            if (\is_callable($callback)) {
                \call_user_func_array($callback, [$return]);
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

        if (!\in_array($connective, [self::SQL_CONDITION_OR, self::SQL_CONDITION_AND])) {
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
     * @return string|bool The transformed operator or false if not supported
     */
    public function transformVerboseOperator(string $verboseOperator)
    {
        switch ($verboseOperator) {
            case static::OPERATOR_LIKE:
                return 'LIKE';

            case static::OPERATOR_UNLIKE:
                return 'NOT LIKE';

            case static::OPERATOR_EQUAL:
                return '=';

            case static::OPERATOR_UNEQUAL:
                return '!=';

            case static::OPERATOR_LOWER:
                return '<';

            case static::OPERATOR_GREATER:
                return '>';

            case static::OPERATOR_LOWER_EQUAL:
                return '<=';

            case static::OPERATOR_GREATER_EQUAL:
                return '>=';

            case static::OPERATOR_IN:
                return 'IN';

                break;

            case static::OPERATOR_NOT_IN:
                return 'NOT IN';

            case static::OPERATOR_IS_NULL:
                return 'NOT IN';

            case static::OPERATOR_IS_NOT_NULL:
                return 'IS NOT NULL';

            case static::OPERATOR_IS_EMPTY:
                return '=""';

            case static::OPERATOR_IS_NOT_EMPTY:
                return '!=""';
        }

        return false;
    }

    /**
     * Computes a MySQL condition appropriate for the given operator.
     *
     * @param mixed  $value
     * @param string $table
     *
     * @return array Returns array($strQuery, $arrValues)
     */
    public function computeCondition(string $field, string $operator, $value, string $table = null, bool $skipTablePrefix = false)
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
                if (\is_array($value)) {
                    foreach ($value as $val) {
                        $values[] = Controller::replaceInsertTags('%'.($addQuotes ? '"'.$val.'"' : $val).'%', false);
                    }

                    break;
                }
                $values[] = Controller::replaceInsertTags('%'.($addQuotes ? '"'.$value.'"' : $value).'%', false);

                break;

            case static::OPERATOR_EQUAL:
                $values[] = Controller::replaceInsertTags(\is_array($value) ? implode(' ', $value) : $value, false);

                break;

            case static::OPERATOR_UNEQUAL:
            case '<>':
                $values[] = Controller::replaceInsertTags(\is_array($value) ? implode(' ', $value) : $value, false);

                break;

            case static::OPERATOR_LOWER:
                $pattern = 'CAST(? AS DECIMAL)';
                $values[] = Controller::replaceInsertTags(\is_array($value) ? implode(' ', $value) : $value, false);

                break;

            case static::OPERATOR_GREATER:
                $pattern = 'CAST(? AS DECIMAL)';
                $values[] = Controller::replaceInsertTags(\is_array($value) ? implode(' ', $value) : $value, false);

                break;

            case static::OPERATOR_LOWER_EQUAL:
                $pattern = 'CAST(? AS DECIMAL)';
                $values[] = Controller::replaceInsertTags(\is_array($value) ? implode(' ', $value) : $value, false);

                break;

            case static::OPERATOR_GREATER_EQUAL:
                $pattern = 'CAST(? AS DECIMAL)';
                $values[] = Controller::replaceInsertTags(\is_array($value) ? implode(' ', $value) : $value, false);

                break;

            case static::OPERATOR_IN:
                $value = array_filter(explode(',', Controller::replaceInsertTags($value, false)));

                // skip if empty to avoid sql error
                if (empty($value)) {
                    break;
                }

                $pattern = '('.implode(
                        ',',
                        array_map(
                            function ($val) {
                                return '"'.addslashes(trim($val)).'"';
                            },
                            $value
                        )
                    ).')';

                break;

            case static::OPERATOR_NOT_IN:
                $value = array_filter(explode(',', Controller::replaceInsertTags($value, false)));

                // skip if empty to avoid sql error
                if (empty($value)) {
                    break;
                }

                $pattern = '('.implode(
                        ',',
                        array_map(
                            function ($val) {
                                return '"'.addslashes(trim($val)).'"';
                            },
                            $value
                        )
                    ).')';

                break;

            case static::OPERATOR_IS_NULL:
            case static::OPERATOR_IS_NOT_NULL:
            case static::OPERATOR_IS_EMPTY:
            case static::OPERATOR_IS_NOT_EMPTY:
                $pattern = '';

                break;

            default:
                if (\is_array($value)) {
                    foreach ($value as $val) {
                        $values[] = Controller::replaceInsertTags('%'.($addQuotes ? '"'.$val.'"' : $val).'%', false);
                    }

                    break;
                }
                $values[] = Controller::replaceInsertTags('%'.($addQuotes ? '"'.$value.'"' : $value).'%', false);

                break;
        }

        $operator = $this->transformVerboseOperator($operator);

        $explodedField = explode('.', $field);

        // remove table if already added to field name
        if (\count($explodedField) > 1) {
            $field = end($explodedField);
        }

        return [(!$skipTablePrefix && $table ? $table.'.' : '')."$field $operator $pattern", $values];
    }

    public function composeWhereForQueryBuilder(QueryBuilder $queryBuilder, string $field, string $operator, array $dca = null, $value = null)
    {
        $wildcard = ':'.str_replace('.', '_', $field);
        $where = '';

        // remove dot for table prefixes
        if (false !== strpos($wildcard, '.')) {
            $wildcard = str_replace('.', '_', $wildcard);
        }

        switch ($operator) {
            case self::OPERATOR_LIKE:
                $where = $queryBuilder->expr()->like($field, $wildcard);
                $queryBuilder->setParameter($wildcard, '%'.Controller::replaceInsertTags(\is_array($value) ? implode(' ', $value) : $value, false).'%');

                break;

            case self::OPERATOR_UNLIKE:
                $where = $queryBuilder->expr()->notLike($field, $wildcard);
                $queryBuilder->setParameter($wildcard, '%'.Controller::replaceInsertTags(\is_array($value) ? implode(' ', $value) : $value, false).'%');

                break;

            case self::OPERATOR_EQUAL:
                $where = $queryBuilder->expr()->eq($field, $wildcard);
                $queryBuilder->setParameter($wildcard, Controller::replaceInsertTags(\is_array($value) ? implode(' ', $value) : $value, false));

                break;

            case self::OPERATOR_UNEQUAL:
                $where = $queryBuilder->expr()->neq($field, $wildcard);
                $queryBuilder->setParameter($wildcard, Controller::replaceInsertTags(\is_array($value) ? implode(' ', $value) : $value, false));

                break;

            case self::OPERATOR_LOWER:
                $where = $queryBuilder->expr()->lt($field, $wildcard);
                $queryBuilder->setParameter($wildcard, Controller::replaceInsertTags(\is_array($value) ? implode(' ', $value) : $value, false));

                break;

            case self::OPERATOR_LOWER_EQUAL:
                $where = $queryBuilder->expr()->lte($field, $wildcard);
                $queryBuilder->setParameter($wildcard, Controller::replaceInsertTags(\is_array($value) ? implode(' ', $value) : $value, false));

                break;

            case self::OPERATOR_GREATER:
                $where = $queryBuilder->expr()->gt($field, $wildcard);
                $queryBuilder->setParameter($wildcard, Controller::replaceInsertTags(\is_array($value) ? implode(' ', $value) : $value, false));

                break;

            case self::OPERATOR_GREATER_EQUAL:
                $where = $queryBuilder->expr()->gte($field, $wildcard);
                $queryBuilder->setParameter($wildcard, Controller::replaceInsertTags(\is_array($value) ? implode(' ', $value) : $value, false));

                break;

            case self::OPERATOR_IN:
                $value = array_filter(!\is_array($value) ? explode(',', $value) : $value);

                // skip if empty to avoid sql error
                if (empty($value)) {
                    break;
                }

                $where = $queryBuilder->expr()->in(
                    $field,
                    array_map(
                        function ($val) {
                            return '"'.addslashes(Controller::replaceInsertTags(trim($val), false)).'"';
                        },
                        $value
                    )
                );

                break;

            case self::OPERATOR_NOT_IN:
                $value = array_filter(!\is_array($value) ? explode(',', $value) : $value);

                // skip if empty to avoid sql error
                if (empty($value)) {
                    break;
                }

                $where = $queryBuilder->expr()->notIn(
                    $field,
                    array_map(
                        function ($val) {
                            $val = Controller::replaceInsertTags(trim($val), false);

                            return '"'.addslashes(Controller::replaceInsertTags(trim($val), false)).'"';
                        },
                        $value
                    )
                );

                break;

            case self::OPERATOR_IS_NULL:
                $where = $queryBuilder->expr()->isNull($field);

                break;

            case self::OPERATOR_IS_NOT_NULL:
                $where = $queryBuilder->expr()->isNotNull($field);

                break;

            case self::OPERATOR_IS_EMPTY:
                $where = $queryBuilder->expr()->eq($field, '\'\'');

                break;

            case self::OPERATOR_IS_NOT_EMPTY:
                $where = $queryBuilder->expr()->neq($field, '\'\'');

                break;

            case self::OPERATOR_REGEXP:
            case self::OPERATOR_NOT_REGEXP:
                $where = $field.(self::OPERATOR_NOT_REGEXP == $operator ? ' NOT REGEXP ' : ' REGEXP ').$wildcard;

                if (\is_array($dca) && isset($dca['eval']['multiple']) && $dca['eval']['multiple']) {
                    // match a serialized blob
                    if (\is_array($value)) {
                        // build a regexp alternative, e.g. (:"1";|:"2";)
                        $queryBuilder->setParameter(
                            $wildcard,
                            '('.implode(
                                '|',
                                array_map(
                                    function ($val) {
                                        return ':"'.Controller::replaceInsertTags($val, false).'";';
                                    },
                                    $value
                                )
                            ).')'
                        );
                    } else {
                        $queryBuilder->setParameter($wildcard, ':"'.Controller::replaceInsertTags($value, false).'";');
                    }
                } else {
                    // TODO: this makes no sense, yet
                    $queryBuilder->setParameter($wildcard, Controller::replaceInsertTags(\is_array($value) ? implode(' ', $value) : $value, false));
                }

                break;
        }

        return $where;
    }

    public function getChildRecords(array $parentIds, string $table, array $options = []): array
    {
        $children = [];
        $db = $this->framework->createInstance(Database::class);
        $sorting = (isset($options['sorting']) && $options['sorting'] ? ' ORDER BY '.$db->findInSet($table.'.pid', $parentIds).', sorting' : false);
        $fetchRows = isset($options['fetchRows']) && $options['fetchRows'];
        $recursive = isset($options['recursive']) && $options['recursive'];

        $childRecords = $db->query('SELECT '.($fetchRows ? '*' : "$table.id, $table.pid")." FROM $table WHERE $table.pid IN(".implode(',', $parentIds).')'.$sorting);

        if ($childRecords->numRows > 0) {
            while ($childRecords->next()) {
                $row = $childRecords->row();
                $children[] = $fetchRows ? $row : $row['id'];
            }

            if ($recursive) {
                $children = array_merge($children, $this->getChildRecords($childRecords->fetchEach('id'), $table, $options));
            }
        }

        return $children;
    }

    /**
     * Returns a database result for a given table and id(primary key).
     *
     * @param mixed $pk
     *
     * @return mixed
     */
    public function findResultByPk(string $table, $pk, array $options = [])
    {
        /* @var Database $adapter */
        if (!($adapter = $this->framework->getAdapter(Database::class))) {
            return null;
        }

        $options = array_merge(
            [
                'limit' => 1,
                'column' => 'id',
                'value' => $pk,
            ],
            $options
        );

        $options['table'] = $table;
        $query = \Contao\Model\QueryBuilder::find($options);

        $statement = $adapter->getInstance()->prepare($query);

        // Defaults for limit and offset
        if (!isset($options['limit'])) {
            $options['limit'] = 0;
        }

        if (!isset($options['offset'])) {
            $options['offset'] = 0;
        }

        // Limit
        if ($options['limit'] > 0 || $options['offset'] > 0) {
            $statement->limit($options['limit'], $options['offset']);
        }

        return $statement->execute($options['value']);
    }

    /**
     * Return a single database result by table and search criteria.
     *
     * @return mixed
     */
    public function findOneResultBy(string $table, array $columns, array $values, array $options = [])
    {
        /* @var Database $adapter */
        if (!($adapter = $this->framework->getAdapter(Database::class))) {
            return null;
        }

        $options = array_merge(
            [
                'limit' => 1,
                'column' => $columns,
                'value' => $values,
            ],
            $options
        );

        $options['table'] = $table;
        $query = \Contao\Model\QueryBuilder::find($options);

        $statement = $adapter->getInstance()->prepare($query);

        if (!isset($options['offset'])) {
            $options['offset'] = 0;
        }

        // Limit
        if ($options['limit'] > 0 || $options['offset'] > 0) {
            $statement->limit($options['limit'], $options['offset']);
        }

        return $statement->execute($options['value']);
    }

    public function findResultsBy(string $table, array $columns, array $values, array $options = [])
    {
        /* @var Database $adapter */
        if (!($adapter = $this->framework->getAdapter(Database::class))) {
            return null;
        }

        $options = array_merge(
            [
                'column' => $columns,
                'value' => $values,
            ],
            $options
        );

        $options['table'] = $table;
        $query = \Contao\Model\QueryBuilder::find($options);

        $statement = $adapter->getInstance()->prepare($query);

        // Defaults for limit and offset
        if (!isset($options['limit'])) {
            $options['limit'] = 0;
        }

        if (!isset($options['offset'])) {
            $options['offset'] = 0;
        }

        // Limit
        if ($options['limit'] > 0 || $options['offset'] > 0) {
            $statement->limit($options['limit'], $options['offset']);
        }

        return $statement->execute($options['value']);
    }

    public function insert(string $table, array $set)
    {
        /* @var Database $adapter */
        if (!($adapter = $this->framework->getAdapter(Database::class))) {
            return null;
        }

        $columnNames = implode(',', array_keys($set));

        $wildcards = implode(',', array_map(function () {
            return '?';
        }, $set));

        $query = "INSERT INTO $table ($columnNames) VALUES ($wildcards)";

        \call_user_func_array([$adapter->getInstance()->prepare($query), 'execute'], array_values($set));
    }

    public function update(string $table, array $set, string $where = null, array $whereValues = [])
    {
        /* @var Database $adapter */
        if (!($adapter = $this->framework->getAdapter(Database::class))) {
            return null;
        }

        $assignments = implode(',', array_map(function ($column) use ($table) {
            return "$column=?";
        }, array_keys($set)));

        $query = "UPDATE $table SET $assignments".($where ? " WHERE $where" : '');

        \call_user_func_array([$adapter->getInstance()->prepare($query), 'execute'], array_merge(array_values($set), $whereValues));
    }

    public function delete(string $table, string $where = null, array $whereValues = [])
    {
        /* @var Database $adapter */
        if (!($adapter = $this->framework->getAdapter(Database::class))) {
            return null;
        }

        $query = "DELETE FROM $table".($where ? " WHERE $where" : '');

        \call_user_func_array([$adapter->getInstance()->prepare($query), 'execute'], $whereValues);
    }
}

<?php

namespace HeimrichHannot\UtilsBundle\Util\Database;

class DatabaseUtil
{
    /**
     * Create a where condition for a field that contains a serialized blob.
     *
     * @param string $field      The field the condition should be checked against accordances
     * @param array  $options    Pass additional options.
     *
     * Options:
     * - inline_values: (bool) Inline the values in the sql part instead of using ? ('REGEXP (':"3"')' instead of 'REGEXP (?)'). Return value not change (array still contains the values)
     * - condition_and: (bool) Use AND instead of OR as connective for the conditions
     *
     * @return array{
     *     column: string,
     *     values: array
     * } An array containing the where condition and the values.
     */
    public function createWhereForSerializedBlob(string $field, array $values, array $options = []): array
    {
        $options = array_merge([
            'condition_and' => false,
            'inline_values' => false,
        ], $options);

        $returnValues = [];
        $connective = $options['condition_and'] ? 'AND' : 'OR';

        $where = '';

        foreach ($values as $val) {
            if (!empty($where)) {
                $where .= " $connective ";
            }

            $value = ":\"$val\"";

            $where .= $options['condition_and'] ? '(' : '';

            $where .= "$field REGEXP (".($options['inline_values'] ? $value : '?').')';

            $where .= $options['condition_and'] ? ')' : '';

            $returnValues[] = $value;
        }

        return ['column' => "($where)", 'values' => $returnValues];
    }
}
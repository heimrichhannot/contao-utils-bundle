<?php

namespace HeimrichHannot\UtilsBundle\Util\DatabaseUtil;

class CreateWhereForSerializedBlobResult
{
    /**
     * @var string
     */
    public $field;
    /**
     * @var array
     */
    public $values;

    public function __construct(
        string $field,
        array $values
    ) {
        $this->field = $field;
        $this->values = $values;
    }

    /**
     * Return the where query with AND operation for each value. Values are inlined in the query ('REGEXP (':"3"')' instead of 'REGEXP (?)').
     */
    public function createInlineAndWhere(): string
    {
        return '('.$this->field.' REGEXP (\''.implode("'\) OR ".$this->field.' REGEXP (\'', $this->getValueList()).'\'))';
    }

    /**
     * Return the where query with OR operation for each value. Values are inlined in the query ('REGEXP (':"3"')' instead of 'REGEXP (?)').
     */
    public function createInlineOrWhere(): string
    {
        return '('.$this->field.' REGEXP (\''.implode("\') OR ".$this->field.' REGEXP (\'', $this->getValueList()).'\'))';
    }

    /**
     * Return the where query with AND operation and placeholder for each value ('REGEXP (?)'). Values can be obtained from the values property.
     */
    public function createAndWhere(): string
    {

        return '('.$this->field.' REGEXP ('.implode(") AND ".$this->field.' REGEXP (', array_fill(0, count($this->getValueList()), '?')).'))';
    }

    /**
     * Return the where query with OR operation and placeholder for each value ('REGEXP (?)'). Values can be obtained from the values property.
     */
    public function createOrWhere(): string
    {
        return '('.$this->field.' REGEXP ('.implode(") OR ".$this->field.' REGEXP (', array_fill(0, count($this->getValueList()), '?')).'))';
    }

    private function getValueList(): array
    {
        $returnValues = [];

        foreach ($this->values as $val) {
            $returnValues[] = ":\"$val\"";
        }

        return $returnValues;
    }

}
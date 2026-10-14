<?php

namespace HeimrichHannot\UtilsBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Dispatched before a search vector is tokenized and stored.
 *
 * Use it to add values that are not stored in a column of the record, for example tags or other relations kept in a
 * separate table, or to remove values that should not be searchable.
 */
class BuildSearchVectorEvent extends Event
{
    /**
     * @param array<string, mixed> $row    The raw database row
     * @param list<string>         $values The formatted, not yet tokenized values. Each value stays a separate
     *                                     section of the vector, so a quoted phrase never matches across two values.
     * @param string               $locale The language of the record. The values have been resolved in this language,
     *                                     and the values of the vector are normalized and stemmed in it as well.
     */
    public function __construct(
        public readonly string $table,
        public readonly array $row,
        private array $values,
        public readonly string $locale,
    ) {}

    /**
     * @return list<string>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @param list<string> $values
     */
    public function setValues(array $values): void
    {
        $this->values = \array_values($values);
    }

    public function addValue(string $value): void
    {
        $this->values[] = $value;
    }
}

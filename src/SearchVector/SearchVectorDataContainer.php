<?php

namespace HeimrichHannot\UtilsBundle\SearchVector;

use Contao\DataContainer;

/**
 * Minimal data container for resolving field values outside the backend, e.g. in the rebuild command.
 *
 * Options callbacks get table, id and the current record, nothing else. getCurrentRecord() returns the given row
 * without the permission check of the backend driver, as there is usually no logged-in user.
 *
 * @internal
 */
class SearchVectorDataContainer extends DataContainer
{
    /**
     * @param array<string, mixed> $row
     */
    public function __construct(string $table, private readonly array $row)
    {
        parent::__construct();

        $this->strTable = $table;
        $this->intId = (int) ($row['id'] ?? 0);
        // For callbacks that still read the deprecated $dc->activeRecord
        $this->objActiveRecord = (object) $row;
    }

    public function getCurrentRecord(int|string|null $id = null, ?string $table = null): ?array
    {
        if ((!$id || (int) $id === $this->intId) && (!$table || $table === $this->strTable)) {
            return $this->row;
        }

        return parent::getCurrentRecord($id, $table);
    }

    public function getPalette(): string
    {
        return '';
    }

    protected function save($varValue): void
    {
    }
}

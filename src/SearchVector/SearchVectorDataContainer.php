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
        // Contao 4.13 callbacks read the record from $dc->activeRecord
        $this->objActiveRecord = (object) $row;
    }

    public function getCurrentRecord(int|string|null $id = null, ?string $table = null): ?array
    {
        if ((!$id || (int) $id === $this->intId) && (!$table || $table === $this->strTable)) {
            return $this->row;
        }

        // Contao 4.13 has no getCurrentRecord()
        /** @phpstan-ignore-next-line Contao 5 only */
        return \method_exists(DataContainer::class, 'getCurrentRecord') ? parent::getCurrentRecord($id, $table) : null;
    }

    public function getPalette(): string
    {
        return '';
    }

    protected function save($varValue): void
    {
    }
}

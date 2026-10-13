<?php

namespace HeimrichHannot\UtilsBundle\SearchVector\Messenger;

/**
 * Requests rebuilding the search vector of a record, see SearchVectorIndexer::queue().
 */
final readonly class RebuildSearchVectorMessage
{
    public function __construct(
        public string $table,
        public int $id,
    ) {}
}

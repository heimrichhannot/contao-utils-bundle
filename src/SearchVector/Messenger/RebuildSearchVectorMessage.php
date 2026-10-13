<?php

namespace HeimrichHannot\UtilsBundle\SearchVector\Messenger;

/**
 * Requests rebuilding the search vector of a record, see SearchVectorIndexer::queue().
 */
final class RebuildSearchVectorMessage
{
    public function __construct(
        public readonly string $table,
        public readonly int $id,
    ) {}
}

<?php

namespace HeimrichHannot\UtilsBundle\SearchVector\Messenger;

use HeimrichHannot\UtilsBundle\SearchVector\SearchVectorIndexer;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
class RebuildSearchVectorMessageHandler
{
    public function __construct(
        private readonly SearchVectorIndexer $indexer,
    ) {}

    public function __invoke(RebuildSearchVectorMessage $message): void
    {
        try {
            $this->indexer->index($message->table, $message->id);
        } catch (\InvalidArgumentException $e) {
            // The table has no search vector field (anymore) or an unknown stemmer or normalizer, retrying won't help
            throw new UnrecoverableMessageHandlingException($e->getMessage(), 0, $e);
        }
    }
}

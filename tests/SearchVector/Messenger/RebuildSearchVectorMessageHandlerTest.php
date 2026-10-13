<?php

namespace HeimrichHannot\UtilsBundle\Tests\SearchVector\Messenger;

use HeimrichHannot\UtilsBundle\SearchVector\Messenger\RebuildSearchVectorMessage;
use HeimrichHannot\UtilsBundle\SearchVector\Messenger\RebuildSearchVectorMessageHandler;
use HeimrichHannot\UtilsBundle\SearchVector\SearchVectorIndexer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

class RebuildSearchVectorMessageHandlerTest extends TestCase
{
    public function testIndexesTheRecord(): void
    {
        $indexer = $this->createMock(SearchVectorIndexer::class);
        $indexer->expects($this->once())->method('index')->with('tl_example', 5)->willReturn(true);

        (new RebuildSearchVectorMessageHandler($indexer))(new RebuildSearchVectorMessage('tl_example', 5));
    }

    public function testConfigurationErrorsAreNotRetried(): void
    {
        $indexer = $this->createMock(SearchVectorIndexer::class);
        $indexer->method('index')->willThrowException(new \InvalidArgumentException('Table "tl_example" has no search vector field.'));

        $this->expectException(UnrecoverableMessageHandlingException::class);
        $this->expectExceptionMessage('Table "tl_example" has no search vector field.');

        (new RebuildSearchVectorMessageHandler($indexer))(new RebuildSearchVectorMessage('tl_example', 5));
    }

    public function testOtherErrorsAreRetried(): void
    {
        $indexer = $this->createMock(SearchVectorIndexer::class);
        $indexer->method('index')->willThrowException(new \RuntimeException('Database gone away'));

        $this->expectException(\RuntimeException::class);

        (new RebuildSearchVectorMessageHandler($indexer))(new RebuildSearchVectorMessage('tl_example', 5));
    }
}

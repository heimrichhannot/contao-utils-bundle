<?php

namespace HeimrichHannot\UtilsBundle\Tests\Command;

use HeimrichHannot\UtilsBundle\Command\SearchVectorRebuildCommand;
use HeimrichHannot\UtilsBundle\SearchVector\SearchVectorIndexer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class SearchVectorRebuildCommandTest extends TestCase
{
    public function testCommandName(): void
    {
        $command = new SearchVectorRebuildCommand($this->createMock(SearchVectorIndexer::class));

        $this->assertSame('huh:utils:search-vector:rebuild', $command->getName());
        $this->assertTrue($command->getDefinition()->getArgument('table')->isRequired());
        $this->assertTrue($command->getDefinition()->getOption('id')->isArray());
    }

    public function testRebuildSelectedIds(): void
    {
        $indexer = $this->createMock(SearchVectorIndexer::class);
        $indexer->expects($this->never())->method('indexAll');
        $indexer->expects($this->exactly(3))->method('index')
            ->willReturnCallback(static fn (string $table, int $id): bool => 2 !== $id);

        $tester = new CommandTester(new SearchVectorRebuildCommand($indexer));
        $status = $tester->execute(['table' => 'tl_example', '--id' => ['1', '2', '3']]);

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertStringContainsString('Rebuilt the search vector of 3 record(s) in tl_example, 2 changed.', $tester->getDisplay());
    }

    public function testRebuildSelectedIdsPassesTableAndIds(): void
    {
        $calls = [];
        $indexer = $this->createMock(SearchVectorIndexer::class);
        $indexer->expects($this->exactly(2))->method('index')->willReturnCallback(
            static function (string $table, int $id) use (&$calls): bool {
                $calls[] = [$table, $id];

                return false;
            }
        );

        $tester = new CommandTester(new SearchVectorRebuildCommand($indexer));
        $tester->execute(['table' => 'tl_example', '--id' => ['5', '7']]);

        $this->assertSame([['tl_example', 5], ['tl_example', 7]], $calls);

        $this->assertStringContainsString('2 record(s) in tl_example, 0 changed.', $tester->getDisplay());
    }

    public function testNonNumericIdsAreIgnored(): void
    {
        $indexer = $this->createMock(SearchVectorIndexer::class);
        $indexer->expects($this->once())->method('index')->with('tl_example', 4)->willReturn(true);

        $tester = new CommandTester(new SearchVectorRebuildCommand($indexer));
        $tester->execute(['table' => 'tl_example', '--id' => ['abc', '0', '4']]);

        $this->assertStringContainsString('1 record(s) in tl_example, 1 changed.', $tester->getDisplay());
    }

    public function testRebuildAll(): void
    {
        $indexer = $this->createMock(SearchVectorIndexer::class);
        $indexer->expects($this->never())->method('index');
        $indexer->expects($this->once())->method('indexAll')
            ->with('tl_example', $this->isType('callable'))
            ->willReturnCallback(static function (string $table, callable $onProgress): int {
                $onProgress(1, true);
                $onProgress(2, false);
                $onProgress(3, true);

                return 2;
            });

        $tester = new CommandTester(new SearchVectorRebuildCommand($indexer));
        $status = $tester->execute(['table' => 'tl_example']);

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertStringContainsString('Rebuilt the search vector of 3 record(s) in tl_example, 2 changed.', $tester->getDisplay());
    }

    public function testRebuildAllWithoutRecords(): void
    {
        $indexer = $this->createMock(SearchVectorIndexer::class);
        $indexer->method('indexAll')->willReturn(0);

        $tester = new CommandTester(new SearchVectorRebuildCommand($indexer));
        $status = $tester->execute(['table' => 'tl_example']);

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertStringContainsString('0 record(s) in tl_example, 0 changed.', $tester->getDisplay());
    }

    public function testInvalidTableFailsWithMessage(): void
    {
        $indexer = $this->createMock(SearchVectorIndexer::class);
        $indexer->method('indexAll')->willThrowException(new \InvalidArgumentException('Table "tl_none" has no search vector field.'));

        $tester = new CommandTester(new SearchVectorRebuildCommand($indexer));
        $status = $tester->execute(['table' => 'tl_none']);

        $this->assertSame(Command::FAILURE, $status);
        $this->assertStringContainsString('Table "tl_none" has no search vector field.', $tester->getDisplay());
        $this->assertStringNotContainsString('Rebuilt', $tester->getDisplay());
    }

    public function testInvalidTableWithIdFailsWithMessage(): void
    {
        $indexer = $this->createMock(SearchVectorIndexer::class);
        $indexer->method('index')->willThrowException(new \InvalidArgumentException('Table "tl_none" has no search vector field.'));

        $tester = new CommandTester(new SearchVectorRebuildCommand($indexer));
        $status = $tester->execute(['table' => 'tl_none', '--id' => ['1']]);

        $this->assertSame(Command::FAILURE, $status);
        $this->assertStringContainsString('has no search vector field', $tester->getDisplay());
    }
}

<?php

namespace HeimrichHannot\UtilsBundle\Command;

use HeimrichHannot\UtilsBundle\SearchVector\SearchVectorIndexer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'huh:utils:search-vector:rebuild',
    description: 'Rebuild the search vector of a table.'
)]
class SearchVectorRebuildCommand extends Command
{
    public function __construct(
        private readonly SearchVectorIndexer $indexer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('table', InputArgument::REQUIRED, 'The table with a search vector field')
            ->addOption('id', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Rebuild only the given record id(s)')
            ->setHelp(
                'Needed after the search vector field was added to a table, and whenever something outside a save of '
                . 'the record changes what the vector should contain, e.g. renamed options or related records.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $table = (string) $input->getArgument('table');

        $idOption = $input->getOption('id');

        // An explicit selection must never fall back to rebuilding the whole table
        if ($invalidIds = \array_filter($idOption, static fn (string $id): bool => !\preg_match('/^[1-9]\d*$/', $id))) {
            $io->error(\sprintf('Invalid record id(s): %s. Pass positive integers to --id.', \implode(', ', $invalidIds)));

            return Command::FAILURE;
        }

        try {
            if ($ids = \array_values(\array_unique(\array_map(intval(...), $idOption)))) {
                $processed = \count($ids);
                $changed = \count(\array_filter(\array_map(fn (int $id): bool => $this->indexer->index($table, $id), $ids)));
            } else {
                $processed = 0;
                $progressBar = $io->createProgressBar();
                $changed = $this->indexer->indexAll($table, static function () use ($progressBar, &$processed): void {
                    ++$processed;
                    $progressBar->advance();
                });
                $progressBar->finish();
                $io->newLine(2);
            }
        } catch (\InvalidArgumentException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success(\sprintf('Rebuilt the search vector of %d record(s) in %s, %d changed.', $processed, $table, $changed));

        return Command::SUCCESS;
    }
}

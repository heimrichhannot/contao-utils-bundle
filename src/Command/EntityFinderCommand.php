<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Command;

use Contao\ArticleModel;
use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\LayoutModel;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\ThemeModel;
use Doctrine\DBAL\Connection;
use HeimrichHannot\UtilsBundle\EntityFinder\EntityFinderHelper;
use HeimrichHannot\UtilsBundle\EntityFinder\Finder;
use HeimrichHannot\UtilsBundle\Event\ExtendEntityFinderEvent;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[AsCommand(
    name: 'huh:utils:entity_finder',
    description: 'A command to find where an entity is included.'
)]
class EntityFinderCommand extends Command
{
    public function __construct(
        private ContaoFramework $contaoFramework,
        private EventDispatcherInterface $eventDispatcher,
        private Connection $connection,
        private EntityFinderHelper $entityFinderHelper,
        private readonly Finder $finder,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('table', InputArgument::REQUIRED, 'The database table')
            ->addArgument('id', InputArgument::REQUIRED, 'The entity id or alias (id is better supported).')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->contaoFramework->initialize();
        $io = new SymfonyStyle($input, $output);

        $io->title('Find entity');

        $table = $input->getArgument('table');
        $id = $input->getArgument('id');

        $result = $this->loop($table, $id);
        $this->output($io, [$result]);
        $io->newLine();

        return 0;
    }

    private function loop(string $table, $id): array
    {
        $current = [
            'table' => $table,
            'id' => $id,
            'parents' => [],
        ];

        $parents = [];

        $this->findEntity($table, $id, $parents);
        $event = $this->runLegacyExtendEntityFinderEvent($table, $id, $parents);

        $this->findInserttags($event);

        $cache = [];

        foreach ($event->getParents() as $parent) {
            if (!isset($parent['table']) || !isset($parent['id'])) {
                continue;
            }
            $cacheKey = $parent['table'].'_'.$parent['id'];

            if (\in_array($cacheKey, $cache)) {
                continue;
            }
            $cache[] = $cacheKey;
            $current['parents'][] = $this->loop($parent['table'], $parent['id']);
        }

        return $current;
    }

    private function output(SymfonyStyle $io, array $tree, string $prepend = '', int $depth = 0): void
    {
        $itemCount = \count($tree);
        $i = 0;

        foreach ($tree as $item) {
            ++$i;

            if ($depth > 0) {
                if ($i === $itemCount) {
                    $newPrepend = $prepend.'└── ';
                    $nextPrepend = $prepend.'    ';
                } else {
                    $newPrepend = $prepend.'├── ';
                    $nextPrepend = $prepend.'│   ';
                }
            } else {
                $newPrepend = $prepend;
                $nextPrepend = $prepend;
            }
            $io->writeln($newPrepend.$this->createText($item['table'], $item['id']));

            if ($item['parents'] ?? false) {
                $this->output($io, $item['parents'], $nextPrepend, ++$depth);
            }
        }
    }

    private function findEntity(string $table, $id, array &$parents, bool $onlyText = false): ?string
    {
        Controller::loadLanguageFile('default');

        switch ($table) {
            case ContentModel::getTable():
                $element = ContentModel::findByIdOrAlias($id);

                if ($element) {
                    $parents[] = ['table' => $element->ptable, 'id' => $element->pid];

                    return 'Content Element: '.($GLOBALS['TL_LANG']['CTE'][$element->type][0] ?? $element->type).' (ID: '.$element->id.', Type: '.$element->type.')';
                }

                return 'Content Element not found: ID '.$id;

            case ArticleModel::getTable():
                $element = ArticleModel::findByPk($id);

                if ($element) {
                    $parents[] = ['table' => PageModel::getTable(), 'id' => $element->pid];

                    if (!$onlyText) {
                        foreach ($this->entityFinderHelper->findModulesByInserttag('html', 'html', 'insert_article', $element->id) as $id) {
                            $parents[] = ['table' => ModuleModel::getTable(), 'id' => $id];
                        }
                        foreach ($this->entityFinderHelper->findContentElementByInserttag('html', 'html', 'insert_article', $element->id) as $id) {
                            $parents[] = ['table' => ContentModel::getTable(), 'id' => $id];
                        }
                    }

                    return 'Article: '.$element->title.' (ID: '.$element->id.')';
                }

                return 'Article not found: ID '.$id;

            case LayoutModel::getTable():
                $layout = LayoutModel::findById($id);

                if ($layout) {
                    $parents[] = ['table' => ThemeModel::getTable(), 'id' => $layout->pid];

                    return 'Layout: '.html_entity_decode($layout->name).' (ID: '.$layout->id.')';
                }

                return 'Layout not found: ID '.$id;

            case ThemeModel::getTable():
                $theme = ThemeModel::findByPk($id);

                if ($theme) {
                    return '<options=bold>Theme: '.$theme->name.'</> (ID: '.$theme->id.')';
                }

                return 'Theme not found: ID '.$id;

            case PageModel::getTable():
                $page = PageModel::findByPk($id);

                if ($page) {
                    return '<options=bold>Page: '.$page->title.'</> (ID: '.$page->id.', Type: '.$page->type.', DNS: '.$page->getFrontendUrl().' )';
                }

                return 'Page not found: ID '.$id;
        }

        $element = $this->finder->find($table, $id);
        if ($element) {
            if ($onlyText) {
                return $element->description;
            }
            if (null === $element->parents) {
                return null;
            }
            foreach ($element->getParents() as $parent) {
                $parents[] = ['table' => $parent['table'], 'id' => $parent['id']];
            }
            return null;
        }

        return null;
    }

    private function createText(string $table, $id): string
    {
        $parents = [];

        if ($text = $this->findEntity($table, $id, $parents, true)) {
            return $text;
        }

        /** @var ExtendEntityFinderEvent $event */
        $event = $this->runLegacyExtendEntityFinderEvent($table, $id, [], true);

        if ($event->getOutput()) {
            return $event->getOutput();
        }

        return 'Unsupported entity: '.$table.' (ID: '.$id.')';
    }

    private function runLegacyExtendEntityFinderEvent(string $table, $id, array $parents, bool $onlyText = false): ExtendEntityFinderEvent
    {

        if ($this->eventDispatcher->hasListeners(ExtendEntityFinderEvent::class)) {
            trigger_deprecation(
                'heimrichhannot/contao-utils-bundle',
                '3.7',
                'Using the ExtendEntityFinderEvent is deprecated. Use EntityFinderFindEvent instead.'
            );
        }
        $event = $this->eventDispatcher->dispatch(
            new ExtendEntityFinderEvent($table, $id, $parents, [], $this->entityFinderHelper, $onlyText),
        );

        return $event;
    }

    private function findInserttags(ExtendEntityFinderEvent $event): void
    {
        $stmt = $this->connection->prepare(
            "SELECT id FROM tl_module WHERE type='html' AND html LIKE ?");

        foreach ($event->getInserttags() as $inserttag) {
            $result = $stmt->executeQuery(['%'.$inserttag.'%']);

            foreach ($result->fetchAllAssociative() as $row) {
                $event->addParent(ModuleModel::getTable(), $row['id']);
            }
        }
    }
}

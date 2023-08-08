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
use HeimrichHannot\UtilsBundle\Event\ExtendEntityFinderEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EntityFinderCommand extends Command
{
    protected static $defaultName = 'huh:utils:entity_finder';
    protected static $defaultDescription = 'A command to find where an entity is included.';

    public function __construct(
        private ContaoFramework $contaoFramework,
        private EventDispatcherInterface $eventDispatcher,
        private Connection $connection,
        private EntityFinderHelper $entityFinderHelper)
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

        if ($input->hasArgument('table') && $input->getArgument('table')) {
            $table = $input->getArgument('table');
        }

        if ($input->hasArgument('id') && $input->getArgument('id')) {
            $id = $input->getArgument('id');
        }

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
        $event = $this->runExtendEntityFinderEvent($table, $id, $parents);

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

                    return 'Article: '.$element->title.' (ID: '.$element->id.')';
                }

                return 'Article not found: ID '.$id;

            case ModuleModel::getTable():
                if ($onlyText) {
                    Controller::loadLanguageFile('modules');
                }
                $element = ModuleModel::findByIdOrAlias($id);

                if ($element) {
                    if (!$onlyText) {
                        $this->findFrontendModuleParents($element, $parents, $id);
                    }

                    return 'Frontend module: '.($GLOBALS['TL_LANG']['FMD'][$element->type][0] ?? $element->type).' (ID: '.$element->id.', Type: '.$element->type.')';
                }

                return 'Frontend module not found: ID '.$id;

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

        return null;
    }

    private function createText(string $table, $id): string
    {
        $parents = [];

        if ($text = $this->findEntity($table, $id, $parents, true)) {
            return $text;
        }

        /** @var ExtendEntityFinderEvent $event */
        $event = $this->runExtendEntityFinderEvent($table, $id, [], true);

        if ($event->getOutput()) {
            return $event->getOutput();
        }

        return 'Unsupported entity: '.$table.' (ID: '.$id.')';
    }

    private function runExtendEntityFinderEvent(string $table, $id, array $parents, bool $onlyText = false): ExtendEntityFinderEvent
    {
        /* @var ExtendEntityFinderEvent $event */
        if (is_subclass_of($this->eventDispatcher, 'Symfony\Contracts\EventDispatcher\EventDispatcherInterface')) {
            $event = $this->eventDispatcher->dispatch(
                new ExtendEntityFinderEvent($table, $id, $parents, [], $this->entityFinderHelper, $onlyText),
                ExtendEntityFinderEvent::class
            );
        } else {
            /** @noinspection PhpParamsInspection */
            $event = $this->eventDispatcher->dispatch(
                ExtendEntityFinderEvent::class,
                new ExtendEntityFinderEvent($table, $id, $parents, [], $this->entityFinderHelper, $onlyText)
            );
        }

        return $event;
    }

    private function findFrontendModuleParents(ModuleModel $module, array &$parents): void
    {
        $contentelements = ContentModel::findBy(['tl_content.type=?', 'tl_content.module=?'], ['module', $module->id]);

        if ($contentelements) {
            foreach ($contentelements as $contentelement) {
                $parents[] = ['table' => ContentModel::getTable(), 'id' => $contentelement->id];
            }
        }

        $result = Database::getInstance()
            ->prepare("SELECT id FROM tl_layout WHERE modules LIKE '%:\"".(string) ((int) $module->id)."\"%'")
            ->execute();

        foreach ($result->fetchEach('id') as $layoutId) {
            $parents[] = ['table' => LayoutModel::getTable(), 'id' => $layoutId];
        }

        $result = Database::getInstance()
            ->prepare("SELECT id FROM tl_module
                        WHERE type='html'
                        AND (
                            html LIKE '%{{insert_module::".$module->id."}}%'
                            OR html LIKE '%{{insert_module::".$module->id."::%')")
            ->execute();

        foreach ($result->fetchEach('id') as $moduleId) {
            $parents[] = ['table' => ModuleModel::getTable(), 'id' => $moduleId];
        }
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

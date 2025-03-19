<?php

namespace HeimrichHannot\UtilsBundle\EntityFinder;

use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FormModel;
use Contao\LayoutModel;
use Contao\ModuleModel;
use Contao\NewsArchiveModel;
use Contao\NewsModel;
use Contao\ThemeModel;
use Doctrine\DBAL\Connection;
use HeimrichHannot\Blocks\BlockModel;
use HeimrichHannot\Blocks\BlockModuleModel;
use HeimrichHannot\UtilsBundle\Event\EntityFinderFindEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use function Symfony\Component\String\u;

/**
 * @internal
 */
class Finder
{

    public function __construct(
        private readonly EntityFinderHelper       $helper,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ContaoFramework          $framework,
        private readonly Connection $connection,

    )
    {
    }

    public function find(string $table, int $id): ?Element
    {
        if (in_array($table, ['find', 'tl_find', 'fallback', 'tl_fallback'])) {
            return null;
        }

        $method = u(str_starts_with($table, 'tl_') ? substr($table, 3) : $table)->camel()->toString();

        if (method_exists($this, $method)) {
            return $this->$method($id);
        }

        $event = $this->eventDispatcher->dispatch(new EntityFinderFindEvent($table, $id));
        if ($element = $event->getElement()) {
            return $element;
        }

        return $this->fallback($table, $id);
    }

    private function fallback(string $table, $idOrAlias): ?Element
    {
        $model = $this->helper->fetchModelOrData($table, $idOrAlias);

        if (null === $model) {
            return null;
        }

        $elementData = [
            'id' => $model->id,
            'table' => $table,
            'description' => null,
            'parents' => null,
        ];

        $this->framework->getAdapter(Controller::class)->loadDataContainer($table);

        $dca = &$GLOBALS['TL_DCA'][$table];

        if (!empty($model->pid)) {
            if (isset($dca['config']['ptable'])) {
                $elementData['parents'] = (function () use ($model, $dca): \Iterator {
                    yield ['table' => $dca['config']['ptable'], 'id' => $model->pid];
                })();
            } elseif (isset($dca['config']['dynamicPtable']) && isset($dca['fields']['pid']) && $model->ptable) {
                $elementData['parents'] = (function () use ($model): \Iterator {
                    yield ['table' => $model->ptable, 'id' => $model->pid];
                })();
            }
        }

        return new Element(
            $elementData['id'],
            $elementData['table'],
            $elementData['description'],
            $elementData['parents']
        );
    }

    /**
     * @codeCoverageIgnore
     */
    private function block(int $id): ?Element
    {
        if (!class_exists(BlockModel::class)) {
            return null;
        }

        $model = $this->helper->fetchModelOrData(BlockModel::getTable(), $id);

        if (null === $model) {
            return null;
        }

        return new Element(
            $model->id,
            BlockModel::getTable(),
            'Block ' . $model->title . ' (ID: ' . $model->id . ')',
            (function () use ($model): \Iterator {
                yield ['table' => ModuleModel::getTable(), 'id' => $model->module];
            })()
        );
    }

    /**
     * @codeCoverageIgnore
     */
    private function blockModule(int $id): ?Element
    {
        if (!class_exists(BlockModuleModel::class)) {
            return null;
        }

        $model = $this->helper->fetchModelOrData(BlockModuleModel::getTable(), $id);

        if (null === $model) {
            return null;
        }

        return new Element(
            $model->id,
            BlockModuleModel::getTable(),
            'Block module ' . $model->title . ' (ID: ' . $model->id . ')',
            (function () use ($model): \Iterator {
                /* @phpstan-ignore class.notFound */
                yield ['table' => BlockModel::getTable(), 'id' => $model->pid];
            })()
        );
    }

    private function form(int $id): ?Element
    {
        $model = $this->helper->fetchModelOrData('tl_form', $id);

        if (null === $model) {
            return null;
        }

        return new Element(
            $model->id,
            'tl_form',
            'Form ' . $model->title . ' (ID: ' . $model->id . ')',
            (function () use ($model): \Iterator {
                foreach (($this->framework->getAdapter(ModuleModel::class)->findByForm($model->id)) ?? [] as $module) {
                    yield ['table' => 'tl_module', 'id' => $module->id];
                }
                foreach (($this->framework->getAdapter(ContentModel::class)->findByForm($model->id)) ?? [] as $element) {
                    yield ['table' => 'tl_content', 'id' => $element->id];
                }
                foreach ($this->helper->findModulesByInserttag('html', 'html', 'insert_form', $model->id) as $module) {
                    yield ['table' => 'tl_module', 'id' => $module->id];
                }
                foreach ($this->helper->findContentElementByInserttag('html', 'html', 'insert_form', $model->id) as $element) {
                    yield ['table' => ContentModel::getTable(), 'id' => $element->id];
                }
            })()
        );
    }

    private function formField(int $id): ?Element
    {
        $model = $this->helper->fetchModelOrData('tl_form_field', $id);

        if (null === $model) {
            return null;
        }
        return new Element(
            $model->id,
            'tl_form_field',
            'Form field ' . $model->name . ' (ID: ' . $model->id . ')',
            (function () use ($model): \Generator {
                yield ['table' => FormModel::getTable(), 'id' => $model->pid];
            })()
        );
    }

    private function module(int $id): ?Element
    {
        $model = $this->helper->fetchModelOrData(ModuleModel::getTable(), $id);

        if (null === $model) {
            return null;
        }

        return new Element(
            $model->id,
            ModuleModel::getTable(),
            'Module ' . $model->type . ' (ID: ' . $model->id . ')',
            (function () use ($model): \Generator {

                yield ['table' => ThemeModel::getTable(), 'id' => $model->pid];

                foreach ($this->framework->getAdapter(ContentModel::class)->findBy(
                    ['tl_content.type=?', 'tl_content.module=?'],
                    ['module', $model->id]
                ) ?? [] as $contentelement) {
                    yield ['table' => ContentModel::getTable(), 'id' => $contentelement->id];
                }

                $result = $this->connection->executeQuery(
                    "SELECT id FROM tl_layout WHERE modules LIKE '%:\"".((int) $model->id)."\"%'"
                );
                foreach ($result->fetchAssociative() as $row) {
                    yield ['id' => $row['id'], 'table' => LayoutModel::getTable()];
                }

                foreach ($this->helper->findModulesByInserttag('html', 'html', 'insert_module', $model->id) as $id) {
                    yield ['id' => $id, 'table' => ModuleModel::getTable()];
                }

                if (class_exists(BlockModuleModel::class) && $blockModules = BlockModuleModel::findByModule($model->id)) {
                    foreach ($blockModules as $blockModule) {
                        yield ['table' => BlockModuleModel::getTable(), 'id' => $blockModule->id];
                    }
                }
            })()
        );
    }

    /**
     * @codeCoverageIgnore
     */
    private function news(int $id): ?Element
    {
        if (!class_exists(NewsModel::class)) {
            return null;
        }

        $model = $this->helper->fetchModelOrData(NewsModel::getTable(), $id);

        if (null === $model) {
            return null;
        }

        return new Element(
            $model->id,
            NewsModel::getTable(),
            'News ' . $model->headline . ' (ID: ' . $model->id . ')',
            (function () use ($model): \Generator {
                /* @phpstan-ignore class.notFound */
                yield ['table' => NewsArchiveModel::getTable(), 'id' => $model->pid];
            })()
        );
    }

    /**
     * @codeCoverageIgnore
     */
    private function newsArchive(int $id): ?Element
    {
        if (!class_exists(NewsArchiveModel::class)) {
            return null;
        }

        $model = $this->helper->fetchModelOrData(NewsArchiveModel::getTable(), $id);

        if (null === $model) {
            return null;
        }

        return new Element(
            $model->id,
            NewsArchiveModel::getTable(),
            'News Archive ' . $model->title . ' (ID: ' . $model->id . ')',
            (function () use ($model): \Generator {
                $modules = $this->helper->findModulesByTypeAndSerializedValue(
                    'newslist',
                    'news_archives',
                    [$model->id]
                );
                if ($modules) {
                    foreach ($modules as $module) {
                        yield ['table' => ModuleModel::getTable(), 'id' => $module->id];
                    }
                }
            })()
        );
    }

    private function listConfig(int $id): ?Element
    {
        $model = $this->helper->fetchModelOrData('tl_list_config', $id);
        if ($model === null) {
            return null;
        }

        return new Element(
            $model->id,
            'tl_list_config',
            'List config ' . $model->title . ' (ID: ' . $model->id . ')',
            (function () use ($model): \Iterator {
                $t = ModuleModel::getTable();
                $modules = $this->framework->getAdapter(ModuleModel::class)->findBy(["$t.type=?", "$t.listConfig=?"], ['listConfig', $model->id]);
                foreach ($modules as $module) {
                    yield ['table' => ModuleModel::getTable(), 'id' => $module->id];
                }
            })()
        );
    }

    private function listConfigElement(int $id): ?Element
    {
        $model = $this->helper->fetchModelOrData('tl_list_config_element', $id);
        if (null === $model) {
            return null;
        }

        return new Element(
            $model->id,
            'tl_list_config_element',
            'List config element ' . $model->title . ' (ID: ' . $model->id . ')',
            (function () use ($model): \Iterator {
                yield ['table' => 'tl_list_config', 'id' => $model->pid];
            })()
        );
    }


}
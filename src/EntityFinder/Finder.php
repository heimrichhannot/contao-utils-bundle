<?php

namespace HeimrichHannot\UtilsBundle\EntityFinder;

use Contao\ContentModel;
use Contao\Controller;
use Contao\DC_Table;
use Contao\FormFieldModel;
use Contao\FormModel;
use Contao\ModuleModel;
use HeimrichHannot\UtilsBundle\Event\EntityFinderFindEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use function Symfony\Component\String\u;

/**
 * @internal
 */
class Finder
{
    private EntityFinderHelper $helper;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityFinderHelper $helper,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->helper = $helper;
        $this->eventDispatcher = $eventDispatcher;
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

        Controller::loadDataContainer($table);
        $dca = &$GLOBALS['TL_DCA'][$table];
        if (!in_array($dca['config']['dataContainer'], ['Table', DC_Table::class])) {
            return new Element(...$elementData);
        }

        if (isset($dca['config']['ptable'])) {
            $elementData['parents'] = function() use ($model, $dca): \Iterator {
                yield ['table' => $dca['config']['ptable'], 'id' => $model->pid];
            };

            return new Element(...$elementData);
        }

        if (isset($dca['config']['dynamicPtable']) && isset($dca['fields']['pid'])) {
            $elementData['parents'] = function() use ($model, $dca): \Iterator {
                yield ['table' => $model->ptable, 'id' => $model->pid];
            };

            return new Element(...$elementData);
        }
    }

    private function form(int $id): ?Element
    {
        $model = FormModel::findByPk($id);
        if ($model === null) {
            return null;
        }

        return new Element(
            $model->id,
            $model->getTable(),
            'Form ' . $model->title. ' (ID: ' . $model->id . ')',
            function() use ($model): \Iterator {
                foreach (ModuleModel::findByForm($model->id) as $model) {
                    yield ['table' => $model::getTable(), 'id' => $model->id];
                }
                foreach (ContentModel::findByForm($model->id) as $model) {
                    yield ['table' => $model::getTable(), 'id' => $model->id];
                }
                foreach ($this->helper->findModulesByInserttag('html', 'html', 'insert_form', $model->id) as $model) {
                    yield ['table' => $model::getTable(), 'id' => $model->id];
                }
                foreach ($this->helper->findContentElementByInserttag('html', 'html', 'insert_form', $model->id) as $model) {
                    yield ['table' => $model::getTable(), 'id' => $model->id];
                }
            }
        );
    }

    private function formField(int $id): ?Element
    {
        $model = FormFieldModel::findByPk($id);
        if ($model === null) {
            return null;
        }
        return new Element(
            $model->id,
            $model->getTable(),
            'Form field ' . $model->name. ' (ID: ' . $model->id . ')',
            function() use ($model): \Iterator {
                yield ['table' => FormModel::getTable(), 'id' => $model->pid];
            }
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
            $model->getTable(),
            'List config ' . $model->title. ' (ID: ' . $model->id . ')',
            function() use ($model): \Iterator {
                $t = ModuleModel::getTable();
                foreach (ModuleModel::findBy(["$t.type=?", "$t.listConfig=?"], ['listConfig', $model->id]) as $module) {
                    yield ['table' => $module::getTable(), 'id' => $module->id];
                }
            }
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
            $model->getTable(),
            'List config element ' . $model->title. ' (ID: ' . $model->id . ')',
            function() use ($model): \Iterator {
                yield ['table' => 'tl_list_config', 'id' => $model->pid];
            }
        );
    }
}
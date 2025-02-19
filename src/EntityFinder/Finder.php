<?php

namespace HeimrichHannot\UtilsBundle\EntityFinder;

use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
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

    public function __construct(
        private readonly EntityFinderHelper       $helper,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ContaoFramework          $framework

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
                $elementData['parents'] = (function () use ($model, $dca): \Iterator {
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
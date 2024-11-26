<?php

namespace HeimrichHannot\UtilsBundle\EntityFinder;

use Contao\ContentModel;
use Contao\FormFieldModel;
use Contao\FormModel;
use Contao\ModuleModel;

class Finder
{
    private EntityFinderHelper $helper;

    public function __construct(
        EntityFinderHelper $helper
    )
    {
        $this->helper = $helper;
    }

    public function find(string $table, int $id): ?Element
    {
        switch ($table) {
            case FormModel::getTable():
                return $this->form($id);
            case FormFieldModel::getTable():
                return $this->formField($id);
        }

        return null;
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
}
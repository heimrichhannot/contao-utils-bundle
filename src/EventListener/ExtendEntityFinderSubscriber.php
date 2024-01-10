<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\EventListener;

use Contao\Model\Collection;
use Contao\ModuleModel;
use Contao\NewsArchiveModel;
use Contao\NewsBundle\ContaoNewsBundle;
use Contao\NewsModel;
use HeimrichHannot\Blocks\BlockModel;
use HeimrichHannot\Blocks\BlockModuleModel;
use HeimrichHannot\UtilsBundle\Event\ExtendEntityFinderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExtendEntityFinderSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ExtendEntityFinderEvent::class => 'onExtendEntityFinderEvent',
        ];
    }

    public function onExtendEntityFinderEvent(ExtendEntityFinderEvent $event)
    {
        $this->findNewsEntity($event);
        $this->findBlockElements($event);
    }

    private function findNewsEntity(ExtendEntityFinderEvent $event): void
    {
        if (!class_exists(ContaoNewsBundle::class)) {
            return;
        }

        switch ($event->getTable()) {
            case NewsModel::getTable():
                $element = NewsModel::findByPk($event->getId());

                if (!$element) {
                    return;
                }

                $event->addParent(NewsArchiveModel::getTable(), $element->pid);
                $event->setOutput('News: '.$element->headline.' (ID: '.$element->id.')');

                break;

            case NewsArchiveModel::getTable():
                $element = NewsArchiveModel::findByPk($event->getId());

                if (!$element) {
                    return;
                }

                if (!$event->isOnlyText()) {
                    if ($modules = $event->getEntityFinderHelper()->findModulesByTypeAndSerializedValue('newslist', 'news_archives', [$element->id])) {
                        while ($modules->next()) {
                            $event->addParent(ModuleModel::getTable(), $modules->id);
                        }
                    }
                }

                $event->setOutput('News Archive: '.$element->title.' (ID: '.$element->id.')');

                break;
        }
    }

    private function findBlockElements(ExtendEntityFinderEvent $event): void
    {
        if (!class_exists(BlockModel::class)) {
            return;
        }

        switch ($event->getTable()) {
            case BlockModuleModel::getTable():
                $element = BlockModuleModel::findByPk($event->getId());

                if (!$element) {
                    return;
                }
                $event->addParent(BlockModel::getTable(), $element->pid);
                $event->setOutput('Block module: '.$element->title.' (ID: '.$element->id.')');

                break;

            case BlockModel::getTable():
                $block = BlockModel::findByPk($event->getId());

                if ($block) {
                    $event->addParent(ModuleModel::getTable(), $block->module);
                    $event->setOutput('Block: '.$block->title.' (ID: '.$block->id.')');
                }

                break;

            case ModuleModel::getTable():
                if ($event->isOnlyText()) {
                    break;
                }

                if (is_numeric($event->getId())) {
                    /** @var BlockModuleModel[]|Collection|null $blockModules */
                    $blockModules = BlockModuleModel::findByModule($event->getId());

                    if ($blockModules) {
                        foreach ($blockModules as $blockModule) {
                            $event->addParent(BlockModuleModel::getTable(), $blockModule->id);
                        }
                    }
                }

                break;
        }
    }
}

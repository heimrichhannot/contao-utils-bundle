<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\EventListener;

use Contao\Model\Collection;
use Contao\ModuleModel;
use HeimrichHannot\Blocks\BlockModel;
use HeimrichHannot\Blocks\BlockModuleModel;
use HeimrichHannot\UtilsBundle\Event\ExtendEntityFinderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExtendEntityFinderSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ExtendEntityFinderEvent::class => 'onExtendEntityFinderEvent'
        ];
    }

    public function onExtendEntityFinderEvent(ExtendEntityFinderEvent $event)
    {
        if (class_exists(BlockModel::class)) {
            switch ($event->getTable()) {
                case BlockModuleModel::getTable():
                    $element = BlockModuleModel::findByPk($event->getId());

                    if (!$element) {
                        return;
                    }
                    $event->addParent(BlockModel::getTable(), $element->id);
                    $event->setOutput('Block module: '.$element->id);

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
}

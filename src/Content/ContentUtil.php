<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Content;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContentUtil
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->framework = $container->get('contao.framework');
        $this->container = $container;
    }

    public function getMultilingualElements($id, $ptable)
    {
        if (null === ($contentElements = $this->container->get('huh.utils.model')->findModelInstancesBy('tl_content', ['tl_content.pid=?', 'tl_content.ptable=?'], [
                $id,
                $ptable,
            ], ['order' => 'tl_content.sorting ASC']))) {
            return '';
        }

        foreach ($contentElements as $contentElement) {
            $types = [
                'colsetStart',
                'colsetPart',
                'colsetEnd',
                'accordionStart',
                'accordionStop',
                'tiny-slider-content-start',
                'tiny-slider-content-separator',
                'tiny-slider-content-stop',
            ];

            $skip = \in_array($contentElement->type, $types);

            if ($this->container->get('huh.utils.dca')->isDcMultilingual('tl_calendar_events') &&
                $GLOBALS['TL_DCA']['tl_calendar_events']['config']['fallbackLang'] !== $GLOBALS['TL_LANGUAGE']) {
                if (!$contentElement->langPid && !$skip) {
                    continue;
                }

                if (!$skip) {
                    $contentElement->id = $contentElement->langPid;
                }
            }

            $result .= Controller::getContentElement($contentElement);
        }

        return $result;
    }
}

<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util\Ui;

use Contao\ContentModel;
use Contao\CoreBundle\Framework\ContaoFramework;

class AccordionUtil
{
    /**
     * @var ContaoFramework
     */
    private $contaoFramework;

    public function __construct(ContaoFramework $contaoFramework)
    {
        $this->contaoFramework = $contaoFramework;
    }

    public function structureAccordionStartStop(array &$data, string $prefix = 'accordion_'): void
    {
        if (!isset($data['id']) || !isset($data['pid']) || !isset($data['ptable'])) {
            return;
        }

        $cacheKey = $data['ptable'].'_'.$data['pid'];

        if (!isset($this->accordionStartStopCache[$cacheKey])) {
            if ($elements = $this->contaoFramework->getAdapter(ContentModel::class)->findPublishedByPidAndTable(
                    $data['pid'],
                    $data['ptable'],
                    ['order' => 'sorting ASC']
                )) {
                $this->accordionStartStopCache[$cacheKey] = $this->generateAccordionLevel($elements->getModels());
            }
        }

        if (isset($this->accordionStartStopCache[$cacheKey][$data['id']])) {
            $data[$prefix.'parentId'] = $this->accordionStartStopCache[$cacheKey][$data['id']]['parent'];
            $data[$prefix.'first'] = $this->accordionStartStopCache[$cacheKey][$data['id']]['first'] ?? false;
            $data[$prefix.'last'] = $this->accordionStartStopCache[$cacheKey][$data['id']]['last'] ?? false;
        }
    }

    private function generateAccordionLevel(array $elements, int &$index = 0, int $level = 0): array
    {
        $flatAccordionList = [];
        $open = false;
        $startElement = null;
        $lastStopElement = null;

        for ($index; $index < \count($elements); ++$index) {
            $element = $elements[$index];

            if ('accordionStart' === $element->type) {
                if ($open) {
                    $flatAccordionList = $flatAccordionList + $this->generateAccordionLevel($elements, $index, ($level + 1));
                } else {
                    $flatAccordionList[$element->id] = [
                        'type' => $element->type,
                        'level' => $level,
                    ];

                    if (!$startElement) {
                        $flatAccordionList[$element->id]['parent'] = $element->id;
                        $startElement = $element;
                        $flatAccordionList[$element->id]['first'] = true;
                    } else {
                        $flatAccordionList[$element->id]['parent'] = $startElement->id;
                    }
                    $open = true;
                }
            } elseif ('accordionStop' === $element->type) {
                if (!$open && 0 !== $level) {
                    if ($lastStopElement) {
                        $flatAccordionList[$lastStopElement->id]['last'] = true;
                    }
                    --$index;

                    return $flatAccordionList;
                }

                if ($open && 0 !== $level) {
                    $lastStopElement = $element;
                }

                $open = false;
                $flatAccordionList[$element->id] = [
                    'parent' => $startElement->id,
                    'type' => $element->type,
                    'level' => $level,
                ];

                if ($index === (\count($elements) - 1) || ($level < 1 && !\in_array($elements[($index + 1)]->type, ['accordionStart', 'accordionStop']))) {
                    $flatAccordionList[$element->id]['last'] = true;
                    $startElement = null;
                }
            }
        }

        return $flatAccordionList;
    }
}

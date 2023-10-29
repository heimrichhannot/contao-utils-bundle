<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util;

use Contao\ContentModel;
use Contao\CoreBundle\Framework\ContaoFramework;

class AccordionUtil
{
    private array $accordionStartStopCache = [];
    private array $accordionSingleCache;

    public function __construct(private ContaoFramework $contaoFramework)
    {
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

    /**
     * Adds the following flags to the template data:
     * - first
     * - last
     * - parentId.
     *
     * This is needed if your want to group multiple single accordion elements into an accordion wrapper like in bootstrap 4.
     *
     * @param array  $data   Data describing the accordion. Usually this is taken from \Contao\Template::getData().
     * @param string $prefix The prefix for the flags
     */
    public function structureAccordionSingle(array &$data, string $prefix = 'accordion_'): void
    {
        if (!isset($data['id']) || !isset($data['pid']) || !isset($data['ptable'])) {
            return;
        }

        $cacheKey = $data['ptable'].'_'.$data['pid'];

        if (!isset($this->accordionSingleCache[$cacheKey])) {
            if ($elements = $this->contaoFramework->getAdapter(ContentModel::class)->findPublishedByPidAndTable(
                $data['pid'],
                $data['ptable'],
                ['order' => 'sorting ASC']
            )) {
                $lastOneIsAccordionSingle = false;
                $elementGroup = [];
                $this->accordionSingleCache[$cacheKey] = [];

                foreach ($elements as $i => $element) {
                    if ('accordionSingle' === $element->type) {
                        $elementGroup[] = $element->row();
                    } else {
                        if ($lastOneIsAccordionSingle) {
                            $this->accordionSingleCache[$cacheKey][] = $elementGroup;
                            $elementGroup = [];
                        }

                        $lastOneIsAccordionSingle = false;

                        continue;
                    }

                    $lastOneIsAccordionSingle = true;

                    if ($i === \count($elements) - 1) {
                        $this->accordionSingleCache[$cacheKey][] = $elementGroup;
                        $elementGroup = [];
                    }
                }
            }
        }

        if (isset($this->accordionSingleCache[$cacheKey]) && \is_array($this->accordionSingleCache[$cacheKey])) {
            foreach ($this->accordionSingleCache[$cacheKey] as $elementGroup) {
                foreach ($elementGroup as $i => $element) {
                    if ($data['id'] == $element['id']) {
                        if (0 === $i) {
                            $data[$prefix.'first'] = true;
                        }

                        if ($i === \count($elementGroup) - 1) {
                            $data[$prefix.'last'] = true;
                        }

                        $data[$prefix.'parentId'] = $elementGroup[0]['id'];

                        break 2;
                    }
                }
            }
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

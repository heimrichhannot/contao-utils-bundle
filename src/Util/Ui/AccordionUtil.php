<?php

namespace HeimrichHannot\UtilsBundle\Util\Ui;

use HeimrichHannot\UtilsBundle\Util\Model\ModelUtil;

class AccordionUtil
{
    /**
     * @var ModelUtil 
     */
    private $modelUtil;

    public function __construct(ModelUtil $modelUtil)
    {
        $this->modelUtil = $modelUtil;
    }

    public function structureAccordionStartStop(array &$data, string $prefix = 'accordion_'): void
    {
        if (!isset($data['id']) || !isset($data['pid']) || !isset($data['ptable'])) {
            return;
        }

        $cacheKey = $data['ptable'].'_'.$data['pid'];

        if (!isset($this->accordionStartStopCache[$cacheKey])) {
            if (null !== ($elements = $this->modelUtil->findModelInstancesBy (
                    'tl_content',
                    ['tl_content.ptable=?', 'tl_content.pid=?', 'tl_content.invisible!=1',],
                    [$data['ptable'], $data['pid'],],
                    ['order' => 'sorting ASC',]
                ))) {
                $this->accordionStartStopCache[$cacheKey] = $this->generateAccordionLevel($elements->getModels());
            }
        }

        if (isset($this->accordionStartStopCache[$cacheKey][$data['id']])) {
            $data[$prefix.'parentId'] = $this->accordionStartStopCache[$cacheKey][$data['id']]['parent'];
            $data[$prefix.'first'] = $this->accordionStartStopCache[$cacheKey][$data['id']]['first'] ?? false;
            $data[$prefix.'last'] = $this->accordionStartStopCache[$cacheKey][$data['id']]['last'] ?? false;
        } else {
            return;
        }
    }

    private function generateAccordionLevel(array $elements, int &$index = 0, int $level = 0): array
    {
        $flatAccordionList = [];
        $open = false;
        $startElement = null;
        $lastStopElement = null;
        for ($index; $index < count($elements); $index++) {
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
                        $startElement                              = $element;
                        $flatAccordionList[$element->id]['first']  = true;
                    } else {
                        $flatAccordionList[$element->id]['parent'] = $startElement->id;
                    }
                    $open = true;
                }
            } elseif ('accordionStop' === $element->type) {
                if (!$open && $level !== 0) {
                    if ($lastStopElement) {
                        $flatAccordionList[$lastStopElement->id]['last'] = true;
                    }
                    $index--;
                    return $flatAccordionList;
                }
                if ($open && $level !== 0) {
                    $lastStopElement = $element;
                }

                $open = false;
                $flatAccordionList[$element->id] = [
                    'parent' => $startElement->id,
                    'type' => $element->type,
                    'level' => $level,
                ];
                if ($index === (count($elements) - 1) || !in_array($elements[($index + 1)]->type, ['accordionStart', 'accordionStop'])) {
                    $flatAccordionList[$element->id]['last'] = true;
                    $startElement = null;
                }
            }
        }
        return $flatAccordionList;
    }
}
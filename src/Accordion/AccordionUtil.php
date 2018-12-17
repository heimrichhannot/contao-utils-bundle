<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Accordion;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;

class AccordionUtil
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * Single cache.
     *
     * @var array
     */
    protected static $accordionSingleCache = [];

    /**
     * Start/Stop cache.
     *
     * @var array
     */
    protected static $accordionStartStopCache = [];

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Adds the following flags to the template data:
     * - first
     * - last
     * - parentId.
     *
     * @param array  $data   Data describing the accordion. Usually this is taken from \Contao\Template::getData().
     * @param string $prefix The prefix for the flags
     */
    public function structureAccordionSingle(array &$data, string $prefix = 'accordion_')
    {
        if (!isset($data['pid'])) {
            if (null !== ($elements = System::getContainer()->get('huh.utils.model')->findModelInstancesBy(
                    'tl_content',
                    [
                        'ptable=?',
                        'tl_content.pid=?',
                        'invisible!=1',
                    ],
                    [
                        'tl_article',
                        $data['pid'],
                    ],
                    [
                        'order' => 'sorting ASC',
                    ]
                ))) {
                $lastOneIsAccordionSingle = false;
                $elementGroup = [];

                foreach ($elements as $i => $element) {
                    if ('accordionSingle' === $element->type) {
                        $elementGroup[] = $element->row();
                    } else {
                        if ($lastOneIsAccordionSingle) {
                            static::$accordionSingleCache[$data['pid']][] = $elementGroup;
                            $elementGroup = [];
                        }

                        $lastOneIsAccordionSingle = false;

                        continue;
                    }

                    $lastOneIsAccordionSingle = true;

                    if ($i === \count($elements) - 1) {
                        static::$accordionSingleCache[$data['pid']][] = $elementGroup;
                        $elementGroup = [];
                    }
                }
            }
        }

        if (isset(static::$accordionSingleCache[$data['pid']]) && is_array(static::$accordionSingleCache[$data['pid']])) {
            foreach (static::$accordionSingleCache[$data['pid']] as $elementGroup) {
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

    /**
     * Adds the following flags to the template data:
     * - first
     * - last
     * - parentId.
     *
     * @param array  $data   Data describing the accordion. Usually this is taken from \Contao\Template::getData().
     * @param string $prefix The prefix for the flags
     */
    public function structureAccordionStartStop(array &$data, string $prefix = 'accordion_')
    {
        if (!isset(self::$accordionStartStopCache[$data['pid']])) {
            if (null !== ($elements = System::getContainer()->get('huh.utils.model')->findModelInstancesBy(
                    'tl_content',
                    [
                        'tl_content.ptable=?',
                        'tl_content.pid=?',
                        'tl_content.invisible!=1',
                    ],
                    [
                        'tl_article',
                        $data['pid'],
                    ],
                    [
                        'order' => 'sorting ASC',
                    ]
                ))) {
                $lastOneIsAccordionStop = false;

                foreach ($elements as $i => $element) {
                    if ('accordionStart' === $element->type) {
                        if (\count(static::$accordionStartStopCache) < 1) {
                            static::$accordionStartStopCache[$data['pid']][] = [];
                        }

                        if ($lastOneIsAccordionStop) {
                            static::$accordionStartStopCache[$data['pid']][\count(static::$accordionStartStopCache[$data['pid']]) - 1][] = $element->row();
                        } else {
                            static::$accordionStartStopCache[$data['pid']][\count(static::$accordionStartStopCache[$data['pid']]) - 1][] = $element->row();
                        }

                        $lastOneIsAccordionStop = false;
                    } elseif ('accordionStop' === $element->type) {
                        static::$accordionStartStopCache[$data['pid']][\count(static::$accordionStartStopCache[$data['pid']]) - 1][] = $element->row();

                        $lastOneIsAccordionStop = true;

                        continue;
                    } elseif ($lastOneIsAccordionStop) {
                        static::$accordionStartStopCache[$data['pid']][] = [];
                        $lastOneIsAccordionStop = false;
                    }
                }

                // remove trailing empty arrays
                $cleaned = [];

                foreach (static::$accordionStartStopCache[$data['pid']] as $elementGroup) {
                    if (!empty($elementGroup)) {
                        $cleaned[] = $elementGroup;
                    }
                }

                static::$accordionStartStopCache[$data['pid']] = $cleaned;
            }
        }

        if (isset(static::$accordionStartStopCache[$data['pid']]) && is_array(static::$accordionStartStopCache[$data['pid']])) {
            foreach (static::$accordionStartStopCache[$data['pid']] as $elementGroup) {
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
}

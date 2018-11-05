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

    protected static $accordionSingleCache = [];
    protected static $accordionSingleCacheBuilt = false;

    protected static $accordionStartStopCache = [];
    protected static $accordionStartStopCacheBuilt = false;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Adds flags like first and last to the template data.
     *
     * @param array $data Data describing the accordion. Usually this is taken from \Contao\Template::getData().
     */
    public function structureAccordionSingle(array &$data)
    {
        if (!static::$accordionSingleCacheBuilt) {
            if (null !== ($elements = System::getContainer()->get('huh.utils.model')->findModelInstancesBy('tl_content', [
                    'ptable=?',
                    'tl_content.pid=?',
                    'invisible!=1',
                ], [
                    'tl_article',
                    $data['pid'],
                ], [
                    'order' => 'sorting ASC',
                ]))) {
                $lastOneIsAccordionSingle = false;
                $elementGroup = [];

                foreach ($elements as $i => $element) {
                    if ('accordionSingle' === $element->type) {
                        $elementGroup[] = $element->row();
                    } else {
                        if ($lastOneIsAccordionSingle) {
                            static::$accordionSingleCache[] = $elementGroup;
                            $elementGroup = [];
                        }

                        $lastOneIsAccordionSingle = false;

                        continue;
                    }

                    $lastOneIsAccordionSingle = true;

                    if ($i === \count($elements) - 1) {
                        static::$accordionSingleCache[] = $elementGroup;
                        $elementGroup = [];
                    }
                }

                static::$accordionSingleCacheBuilt = true;
            }
        }

        foreach (static::$accordionSingleCache as $elementGroup) {
            foreach ($elementGroup as $i => $element) {
                if ($data['id'] == $element['id']) {
                    if (0 === $i) {
                        $data['first'] = true;
                    }

                    if ($i === \count($elementGroup) - 1) {
                        $data['last'] = true;
                    }

                    break 2;
                }
            }
        }
    }

    /**
     * Adds flags like first, last, startSection and stopSection to the template data.
     *
     * @param array $data Data describing the accordion. Usually this is taken from \Contao\Template::getData().
     */
    public function structureAccordionStartStop(array &$data)
    {
        if (!static::$accordionStartStopCacheBuilt) {
            if (null !== ($elements = System::getContainer()->get('huh.utils.model')->findModelInstancesBy('tl_content', [
                    'tl_content.ptable=?',
                    'tl_content.pid=?',
                    'tl_content.invisible!=1',
                ], [
                    'tl_article',
                    $data['pid'],
                ], [
                    'order' => 'sorting ASC',
                ]))) {
                $lastOneIsAccordionStop = false;

                foreach ($elements as $i => $element) {
                    if ('accordionStart' === $element->type) {
                        if (\count(static::$accordionStartStopCache) < 1) {
                            static::$accordionStartStopCache[] = [];
                        }

                        if ($lastOneIsAccordionStop) {
                            static::$accordionStartStopCache[\count(static::$accordionStartStopCache) - 1][] = $element->row();
                        } else {
                            static::$accordionStartStopCache[\count(static::$accordionStartStopCache) - 1][] = $element->row();
                        }

                        $lastOneIsAccordionStop = false;
                    } elseif ('accordionStop' === $element->type) {
                        static::$accordionStartStopCache[\count(static::$accordionStartStopCache) - 1][] = $element->row();

                        $lastOneIsAccordionStop = true;

                        continue;
                    } elseif ($lastOneIsAccordionStop) {
                        static::$accordionStartStopCache[] = [];
                        $lastOneIsAccordionStop = false;
                    }
                }

                // remove trailing empty arrays
                $cleaned = [];

                foreach (static::$accordionStartStopCache as $elementGroup) {
                    if (!empty($elementGroup)) {
                        $cleaned[] = $elementGroup;
                    }
                }

                static::$accordionStartStopCache = $cleaned;

                static::$accordionStartStopCacheBuilt = true;
            }
        }

        foreach (static::$accordionStartStopCache as $elementGroup) {
            foreach ($elementGroup as $i => $element) {
                if ($data['id'] == $element['id']) {
                    if (0 === $i) {
                        $data['first'] = true;
                    }

                    if ('accordionStart' == $element['type']) {
                        $data['startSection'] = true;
                    }

                    if ('accordionStop' == $element['type']) {
                        $data['stopSection'] = true;
                    }

                    if ($i === \count($elementGroup) - 1) {
                        $data['last'] = true;
                    }

                    break 2;
                }
            }
        }
    }
}

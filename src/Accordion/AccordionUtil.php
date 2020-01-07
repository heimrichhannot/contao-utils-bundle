<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Accordion;

use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AccordionUtil
{
    /**
     * @var ContaoFramework
     */
    protected $framework;

    /**
     * Single cache.
     *
     * @var array
     */
    protected $accordionSingleCache = [];

    /**
     * Start/Stop cache.
     *
     * @var array
     */
    protected $accordionStartStopCache = [];
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->framework = $container->get('contao.framework');
        $this->container = $container;
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
        if (!isset($data['id']) || !isset($data['pid'])) {
            return;
        }

        $cacheKey = $data['ptable'].'_'.$data['pid'];

        if (!isset($this->accordionSingleCache[$cacheKey])) {
            if (null !== ($elements = $this->container->get('huh.utils.model')->findModelInstancesBy(
                    'tl_content',
                    [
                        'tl_content.ptable=?',
                        'tl_content.pid=?',
                        'tl_content.invisible!=1',
                    ],
                    [
                        $data['ptable'],
                        $data['pid'],
                    ],
                    [
                        'order' => 'sorting ASC',
                    ]
                ))) {
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
        if (!isset($data['id']) || !isset($data['pid'])) {
            return;
        }

        $cacheKey = $data['ptable'].'_'.$data['pid'];

        if (!isset($this->accordionStartStopCache[$cacheKey])) {
            if (null !== ($elements = $this->container->get('huh.utils.model')->findModelInstancesBy(
                    'tl_content',
                    [
                        'tl_content.ptable=?',
                        'tl_content.pid=?',
                        'tl_content.invisible!=1',
                    ],
                    [
                        $data['ptable'],
                        $data['pid'],
                    ],
                    [
                        'order' => 'sorting ASC',
                    ]
                ))) {
                $lastOneIsAccordionStop = false;
                $this->accordionStartStopCache[$cacheKey] = [];

                foreach ($elements as $i => $element) {
                    if ('accordionStart' === $element->type) {
                        if (\count($this->accordionStartStopCache[$cacheKey]) < 1) {
                            $this->accordionStartStopCache[$cacheKey][] = [];
                        }

                        $this->accordionStartStopCache[$cacheKey][\count($this->accordionStartStopCache[$cacheKey]) - 1][] = $element->row();

                        $lastOneIsAccordionStop = false;
                    } elseif ('accordionStop' === $element->type) {
                        $this->accordionStartStopCache[$cacheKey][\count($this->accordionStartStopCache[$cacheKey]) - 1][] = $element->row();

                        $lastOneIsAccordionStop = true;

                        continue;
                    } elseif ($lastOneIsAccordionStop) {
                        $this->accordionStartStopCache[$cacheKey][] = [];
                        $lastOneIsAccordionStop = false;
                    }
                }

                // remove trailing empty arrays
                $cleaned = [];

                foreach ($this->accordionStartStopCache[$cacheKey] as $elementGroup) {
                    if (!empty($elementGroup)) {
                        $cleaned[] = $elementGroup;
                    }
                }

                $this->accordionStartStopCache[$cacheKey] = $cleaned;
            }
        }

        if (isset($this->accordionStartStopCache[$cacheKey]) && \is_array($this->accordionStartStopCache[$cacheKey])) {
            foreach ($this->accordionStartStopCache[$cacheKey] as $elementGroup) {
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

<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\EventListener;

use Psr\Container\ContainerInterface;

class FrontendPageListener
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * FrontendPageListener constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Listen on modifyFrontendPage hook.
     *
     * @param $strBuffer
     * @param $strTemplate
     *
     * @return mixed
     */
    public function modifyFrontendPage($strBuffer, $strTemplate)
    {
        return $this->container->get('huh.utils.string')->ensureLineBreaks($strBuffer, $GLOBALS['TL_LANGUAGE']);
    }
}

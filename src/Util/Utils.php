<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util;

use HeimrichHannot\UtilsBundle\Util\Container\ContainerUtil;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;

class Utils implements ServiceSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    protected $locator;

    /**
     * Utils constructor.
     */
    public function __construct(ContainerInterface $locator)
    {
        $this->locator = $locator;
    }

    public static function getSubscribedServices()
    {
        return [
            ContainerUtil::class,
        ];
    }

    public function container(): ContainerUtil
    {
        return $this->locator->get(ContainerUtil::class);
    }
}

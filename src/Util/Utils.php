<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util;

use HeimrichHannot\UtilsBundle\Util\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Util\Locale\LocaleUtil;
use HeimrichHannot\UtilsBundle\Util\String\StringUtil;
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
            LocaleUtil::class,
            StringUtil::class,
        ];
    }

    public function container(): ContainerUtil
    {
        return $this->locator->get(ContainerUtil::class);
    }

    public function locale(): LocaleUtil
    {
        return $this->locator->get(LocaleUtil::class);
    }

    public function string(): StringUtil
    {
        return $this->locator->get(StringUtil::class);
    }
}
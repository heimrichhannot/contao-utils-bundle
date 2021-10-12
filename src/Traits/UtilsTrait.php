<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Traits;

use HeimrichHannot\UtilsBundle\Util\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Util\Locale\LocaleUtil;
use HeimrichHannot\UtilsBundle\Util\Request\RequestUtil;
use HeimrichHannot\UtilsBundle\Util\String\StringUtil;
use Psr\Container\ContainerInterface;

/**
 * Trait UtilsTrait.
 *
 * @internal This trait is only for usage in Utils class. It will be removed when symfony 3 support is dropped.
 */
trait UtilsTrait
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

    public function container(): ContainerUtil
    {
        return $this->locator->get(ContainerUtil::class);
    }

    public function locale(): LocaleUtil
    {
        return $this->locator->get(LocaleUtil::class);
    }

    public function request(): RequestUtil
    {
        return $this->locator->get(RequestUtil::class);
    }

    public function string(): StringUtil
    {
        return $this->locator->get(StringUtil::class);
    }

    public static function getSubscribedServices()
    {
        return [
            ContainerUtil::class,
            LocaleUtil::class,
            RequestUtil::class,
            StringUtil::class,
        ];
    }
}

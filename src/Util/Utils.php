<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util;

use HeimrichHannot\UtilsBundle\Util\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Util\Locale\LocaleUtil;
use HeimrichHannot\UtilsBundle\Util\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Util\Request\RequestUtil;
use HeimrichHannot\UtilsBundle\Util\String\StringUtil;
use HeimrichHannot\UtilsBundle\Util\Ui\AccordionUtil;
use HeimrichHannot\UtilsBundle\Util\User\UserUtil;
use Psr\Container\ContainerInterface;

class Utils extends AbstractServiceSubscriber
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

    public function accordion(): AccordionUtil
    {
        return $this->locator->get(AccordionUtil::class);
    }

    public function array(): ArrayUtil
    {
        return $this->locator->get(ArrayUtil::class);
    }

    public function container(): ContainerUtil
    {
        return $this->locator->get(ContainerUtil::class);
    }

    public function locale(): LocaleUtil
    {
        return $this->locator->get(LocaleUtil::class);
    }

    public function model(): ModelUtil
    {
        return $this->locator->get(ModelUtil::class);
    }

    public function request(): RequestUtil
    {
        return $this->locator->get(RequestUtil::class);
    }

    public function string(): StringUtil
    {
        return $this->locator->get(StringUtil::class);
    }

    public function user(): UserUtil
    {
        return $this->locator->get(UserUtil::class);
    }

    public static function getSubscribedServices()
    {
        return [
            AccordionUtil::class,
            ArrayUtil::class,
            ContainerUtil::class,
            LocaleUtil::class,
            ModelUtil::class,
            RequestUtil::class,
            StringUtil::class,
            UserUtil::class,
        ];
    }
}

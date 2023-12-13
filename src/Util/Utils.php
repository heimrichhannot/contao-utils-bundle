<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util;

use HeimrichHannot\UtilsBundle\Util\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Util\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Util\File\FileUtil;
use HeimrichHannot\UtilsBundle\Util\Html\HtmlUtil;
use HeimrichHannot\UtilsBundle\Util\Locale\LocaleUtil;
use HeimrichHannot\UtilsBundle\Util\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Util\Request\RequestUtil;
use HeimrichHannot\UtilsBundle\Util\Request\UrlUtil;
use HeimrichHannot\UtilsBundle\Util\Routing\RoutingUtil;
use HeimrichHannot\UtilsBundle\Util\Type\ArrayUtil;
use HeimrichHannot\UtilsBundle\Util\Type\StringUtil;
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

    public function class(): ClassUtil
    {
        return $this->locator->get(ClassUtil::class);
    }

    public function container(): ContainerUtil
    {
        return $this->locator->get(ContainerUtil::class);
    }

    public function database(): DatabaseUtil
    {
        return $this->locator->get(DatabaseUtil::class);
    }

    public function dca(): DcaUtil
    {
        return $this->locator->get(DcaUtil::class);
    }

    public function file(): FileUtil
    {
        return $this->locator->get(FileUtil::class);
    }

    public function html(): HtmlUtil
    {
        return $this->locator->get(HtmlUtil::class);
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

    public function routing(): RoutingUtil
    {
        return $this->locator->get(RoutingUtil::class);
    }

    public function string(): StringUtil
    {
        return $this->locator->get(StringUtil::class);
    }

    public function url(): UrlUtil
    {
        return $this->locator->get(UrlUtil::class);
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
            ClassUtil::class,
            ContainerUtil::class,
            DatabaseUtil::class,
            DcaUtil::class,
            FileUtil::class,
            HtmlUtil::class,
            LocaleUtil::class,
            ModelUtil::class,
            RequestUtil::class,
            RoutingUtil::class,
            StringUtil::class,
            UrlUtil::class,
            UserUtil::class,
        ];
    }
}

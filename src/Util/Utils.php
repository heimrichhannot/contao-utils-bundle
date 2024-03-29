<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util;

use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class Utils implements ServiceSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $locator;

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

    public function anonymize(): AnonymizeUtil
    {
        return $this->locator->get(AnonymizeUtil::class);
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

    public function formatter(): FormatterUtil
    {
        return $this->locator->get(FormatterUtil::class);
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

    public static function getSubscribedServices(): array
    {
        return [
            AccordionUtil::class,
            AnonymizeUtil::class,
            ArrayUtil::class,
            ClassUtil::class,
            ContainerUtil::class,
            DatabaseUtil::class,
            DcaUtil::class,
            FileUtil::class,
            FormatterUtil::class,
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

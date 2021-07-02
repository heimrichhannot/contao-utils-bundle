<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\UtilsBundle\Traits;


use HeimrichHannot\UtilsBundle\Util\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Util\Locale\LocaleUtil;
use HeimrichHannot\UtilsBundle\Util\String\StringUtil;
use Psr\Container\ContainerInterface;

/**
 * Trait UtilsTrait
 * @package HeimrichHannot\UtilsBundle\Traits
 *
 * @internal This trait is only for usage in Utils class. It will be removed when symofony 3 support is dropped.
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

    public function string(): StringUtil
    {
        return $this->locator->get(StringUtil::class);
    }

    public static function getSubscribedServices()
    {
        return [
            ContainerUtil::class,
            LocaleUtil::class,
            StringUtil::class,
        ];
    }
}
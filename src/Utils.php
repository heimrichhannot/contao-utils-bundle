<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\UtilsBundle;


use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Image\ImageUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Template\TemplateUtil;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class Utils implements ServiceSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $locator;

    /**
     * Utils constructor.
     */
    public function __construct(ContainerInterface $locator)
    {
        $this->locator = $locator;
    }


    /**
     * @inheritDoc
     */
    public static function getSubscribedServices()
    {
        return [
            ContainerUtil::class,
            ImageUtil::class,
            ModelUtil::class,
            TemplateUtil::class,
        ];
    }

    public function getUtil(string $utilClass)
    {
        if ($this->locator->has($utilClass)) {
            /** @var ServiceLocator|ContainerInterface $handler */
            return $this->locator->get($utilClass);
        }
    }

    /**
     * @return ContainerUtil
     */
    public function container()
    {
        return $this->getUtil(ContainerUtil::class);
    }

    /**
     * @return ImageUtil
     */
    public function image()
    {
        return $this->getUtil(ImageUtil::class);
    }

    /**
     * Return a model util instance
     *
     * @return ModelUtil
     */
    public function model()
    {
        return $this->getUtil(ModelUtil::class);
    }

    /**
     * @return TemplateUtil
     */
    public function template()
    {
        return $this->getUtil(TemplateUtil::class);
    }
}
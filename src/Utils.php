<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle;

use HeimrichHannot\UtilsBundle\Accordion\AccordionUtil;
use HeimrichHannot\UtilsBundle\Arrays\ArrayUtil;
use HeimrichHannot\UtilsBundle\Cache\DatabaseCacheUtil;
use HeimrichHannot\UtilsBundle\Cache\DatabaseTreeCache;
use HeimrichHannot\UtilsBundle\Cache\RemoteImageCache;
use HeimrichHannot\UtilsBundle\Classes\ClassUtil;
use HeimrichHannot\UtilsBundle\Comparison\CompareUtil;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Date\DateUtil;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\File\FileArchiveUtil;
use HeimrichHannot\UtilsBundle\File\FileStorageUtil;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\File\FolderUtil;
use HeimrichHannot\UtilsBundle\Form\FormUtil;
use HeimrichHannot\UtilsBundle\Image\ImageUtil;
use HeimrichHannot\UtilsBundle\Location\LocationUtil;
use HeimrichHannot\UtilsBundle\Member\MemberUtil;
use HeimrichHannot\UtilsBundle\Model\CfgTagModel;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Module\ModuleUtil;
use HeimrichHannot\UtilsBundle\Template\TemplateUtil;
use Psr\Container\ContainerInterface;
use SebastianBergmann\CodeCoverage\Report\Xml\File;
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
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return [
            AccordionUtil::class,
            ArrayUtil::class,
            CfgTagModel::class,
            ClassUtil::class,
            CompareUtil::class,
            ContainerUtil::class,
            DatabaseUtil::class,
            DatabaseCacheUtil::class,
            DatabaseTreeCache::class,
            DateUtil::class,
            DcaUtil::class,
            FileArchiveUtil::class,
            FileStorageUtil::class,
            FileUtil::class,
            FolderUtil::class,
            FormUtil::class,
            ImageUtil::class,
            LocationUtil::class,
            MemberUtil::class,
            ModelUtil::class,
            ModuleUtil::class,
            RemoteImageCache::class,
            TemplateUtil::class,
        ];
    }

    /**
     * Return a util service.
     *
     * @return mixed
     */
    public function getUtil(string $utilClass)
    {
        if ($this->locator->has($utilClass)) {
            /* @var ServiceLocator|ContainerInterface $handler */
            return $this->locator->get($utilClass);
        }
    }

    /**
     * @return AccordionUtil
     */
    public function accordion()
    {
        return $this->getUtil(AccordionUtil::class);
    }

    /**
     * @return ArrayUtil
     */
    public function array()
    {
        return $this->getUtil(ArrayUtil::class);
    }

    /**
     * @return CfgTagModel
     */
    public function cfgTag()
    {
        return $this->getUtil(CfgTagModel::class);
    }

    /**
     * @return CompareUtil
     */
    public function compare()
    {
        return $this->getUtil(CompareUtil::class);
    }

    /**
     * @return ContainerUtil
     */
    public function container()
    {
        return $this->getUtil(ContainerUtil::class);
    }

    /**
     * @return ClassUtil
     */
    public function class()
    {
        return $this->getUtil(ClassUtil::class);
    }

    /**
     * @return DatabaseUtil
     */
    public function database()
    {
        return $this->getUtil(DatabaseUtil::class);
    }

    /**
     * @return DatabaseCacheUtil
     */
    public function databaseCache()
    {
        return $this->getUtil(DatabaseCacheUtil::class);
    }

    /**
     * @return DatabaseTreeCache
     */
    public function datebaseTreeCache()
    {
        return $this->getUtil(DatabaseTreeCache::class);
    }

    /**
     * @return DateUtil
     */
    public function date()
    {
        return $this->getUtil(DateUtil::class);
    }

    /**
     * @return DcaUtil
     */
    public function dca()
    {
        return $this->getUtil(DcaUtil::class);
    }

    /**
     * @return FileArchiveUtil
     */
    public function fileArchive()
    {
        return $this->getUtil(FileArchiveUtil::class);
    }

    /**
     * @return FileStorageUtil
     */
    public function fileStorage()
    {
        return $this->getUtil(FileStorageUtil::class);
    }

    /**
     * @return FileUtil
     */
    public function file()
    {
        return $this->getUtil(FileUtil::class);
    }

    /**
     * @return FolderUtil
     */
    public function folder()
    {
        return $this->getUtil(FolderUtil::class);
    }

    /**
     * @return FormUtil
     */
    public function form()
    {
        return $this->getUtil(FormUtil::class);
    }

    /**
     * @return ImageUtil
     */
    public function image()
    {
        return $this->getUtil(ImageUtil::class);
    }

    /**
     * @return LocationUtil
     */
    public function location()
    {
        return $this->getUtil(LocationUtil::class);
    }

    /**
     * @return MemberUtil
     */
    public function member()
    {
        return $this->getUtil(MemberUtil::class);
    }

    /**
     * Return a model util instance.
     *
     * @return ModelUtil
     */
    public function model()
    {
        return $this->getUtil(ModelUtil::class);
    }

    /**
     * @return ModuleUtil
     */
    public function module()
    {
        return $this->getUtil(ModuleUtil::class);
    }

    /**
     * @return RemoteImageCache
     */
    public function remoteImageCache()
    {
        return $this->getUtil(RemoteImageCache::class);
    }

    /**
     * @return TemplateUtil
     */
    public function template()
    {
        return $this->getUtil(TemplateUtil::class);
    }
}

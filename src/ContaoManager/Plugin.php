<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ConfigPluginInterface;
use HeimrichHannot\UtilsBundle\HeimrichHannotUtilsBundle;
use Symfony\Component\Config\Loader\LoaderInterface;

class Plugin implements BundlePluginInterface, ConfigPluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(HeimrichHannotUtilsBundle::class)->setLoadAfter([
                ContaoCoreBundle::class,
            ]),
        ];
    }

    /**
     * Allows a plugin to load container configuration.
     */
    public function registerContainerConfiguration(LoaderInterface $loader, array $managerConfig)
    {
        $loader->load('@HeimrichHannotUtilsBundle/Resources/config/choices.yml');
        $loader->load('@HeimrichHannotUtilsBundle/Resources/config/parameters.yml');
        $loader->load('@HeimrichHannotUtilsBundle/Resources/config/services.yml');
        $loader->load('@HeimrichHannotUtilsBundle/Resources/config/twig.yml');
        $loader->load('@HeimrichHannotUtilsBundle/Resources/config/utils.yml');

        if (class_exists('HeimrichHannot\EncoreBundle\HeimrichHannotContaoEncoreBundle')) {
            $loader->load('@HeimrichHannotUtilsBundle/Resources/config/config_encore.yml');
        }
    }
}

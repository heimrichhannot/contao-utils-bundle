<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Container;

use Contao\CoreBundle\Monolog\ContaoContext;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ContainerUtil.
 *
 * @deprecated use utils service instead
 */
class ContainerUtil
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var Utils
     */
    protected $utils;

    public function __construct(ContainerInterface $container, Utils $utils)
    {
        $this->container = $container;
        $this->utils = $utils;
    }

    /**
     * Returns the active bundles.
     *
     * @codeCoverageIgnore
     *
     * @deprecated Use kernel.bundles parameter or KernelInterface::getBundles()
     */
    public function getActiveBundles(): array
    {
        return $this->container->getParameter('kernel.bundles');
    }

    /**
     * Checks if some bundle is active. Pass in the class name (e.g. 'HeimrichHannot\FilterBundle\HeimrichHannotContaoFilterBundle' or the legacy Contao 3 name like 'news').
     *
     * @return bool
     *
     * @codeCoverageIgnore
     *
     * @deprecated Use utils service instead
     */
    public function isBundleActive(string $bundleName)
    {
        return $this->utils->container()->isBundleActive($bundleName);
    }

    /**
     * @return bool
     *
     * @codeCoverageIgnore
     *
     * @deprecated Use utils service instead
     */
    public function isBackend()
    {
        return $this->utils->container()->isBackend();
    }

    /**
     * @return bool
     *
     * @codeCoverageIgnore
     *
     * @deprecated Use utils service instead
     */
    public function isFrontend()
    {
        return $this->utils->container()->isFrontend();
    }

    /**
     * @return bool
     *
     * @codeCoverageIgnore
     *
     * @deprecated Use utils service instead
     */
    public function isFrontendCron()
    {
        return $this->utils->container()->isFrontendCron();
    }

    /**
     * @return bool
     *
     * @codeCoverageIgnore
     *
     * @deprecated Use utils service instead
     */
    public function isInstall()
    {
        return $this->utils->container()->isInstall();
    }

    /**
     * @return bool
     *
     * @codeCoverageIgnore
     *
     * @deprecated Use utils service instead
     */
    public function isDev()
    {
        return $this->utils->container()->isDev();
    }

    /**
     * @return mixed
     *
     * @codeCoverageIgnore
     *
     * @deprecated Use RequestStack::getCurrentRequest() instead
     */
    public function getCurrentRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }

    /**
     * @param string $category Use constants in ContaoContext
     *
     * @codeCoverageIgnore
     *
     * @deprecated Use utils service instead
     */
    public function log(string $text, string $function, string $category)
    {
        $this->utils->container()->log($text, $function, $category);
    }

    /**
     * Returns the project root path.
     *
     * @return mixed
     *
     * @codeCoverageIgnore
     *
     * @deprecated Use KernelInterface::getProjectDir or kernel.project_dir parameter
     */
    public function getProjectDir()
    {
        return $this->container->getParameter('kernel.project_dir');
    }

    /**
     * Returns the web folder path.
     *
     * @return mixed
     *
     * @codeCoverageIgnore
     *
     * @deprecated Use contao.web_dir parameter
     */
    public function getWebDir()
    {
        return $this->container->getParameter('contao.web_dir');
    }

    /**
     * Returns the path to the bundle in vendor folder
     * Attention: resolves symlinks!
     *
     * @param string $bundleClass The bundle class class constant (VendorMyBundle::class)
     *
     * @return bool|string False on error
     *
     * @codeCoverageIgnore
     *
     * @deprecated Use utils service instead
     */
    public function getBundlePath(string $bundleClass)
    {
        $result = $this->utils->container()->getBundlePath($bundleClass);

        if (null === $result) {
            return false;
        }

        return $result;
    }

    /**
     * Returns the path or paths to a ressource within a bundle
     * Attention: resolves symlinks!
     *
     * @param string $bundleClass   The bundle class class constant (VendorMyBundle::class)
     * @param string $ressourcePath a ressource or path to ressource
     * @param bool   $first         Returns only first occurrence if multiple paths found
     *
     * @return bool|string|array False on error
     *
     * @codeCoverageIgnore
     *
     * @deprecated Use utils service instead
     */
    public function getBundleResourcePath(string $bundleClass, string $ressourcePath = '', $first = false)
    {
        $result = $this->utils->container()->getBundleResourcePath($bundleClass, $ressourcePath, $first);

        if (null === $result) {
            return false;
        }

        return $result;
    }

    /**
     * Recursively merges a config.yml with a $extensionConfigs array in the context of ExtensionPluginInterface::getExtensionConfig().
     * Must be static, because on Plugin::getExtensionConfig() no contao.framework nor service huh.utils.container is available.
     *
     * @return array
     *
     * @codeCoverageIgnore
     *
     * @deprecated Use ConfigPluginInterface with class_exist instead
     */
    public static function mergeConfigFile(
        string $activeExtensionName,
        string $extensionName,
        array $extensionConfigs,
        string $configFile
    ) {
        if ($activeExtensionName === $extensionName && file_exists($configFile)) {
            $config = Yaml::parseFile($configFile);

            $extensionConfigs = array_merge_recursive(\is_array($extensionConfigs) ? $extensionConfigs : [], \is_array($config) ? $config : []);
        }

        return $extensionConfigs;
    }

    /**
     * @return bool
     *
     * @codeCoverageIgnore
     *
     * @deprecated Use utils service instead
     */
    public function isMaintenanceModeActive()
    {
        return $this->utils->container()->isMaintenanceModeActive();
    }

    /**
     * @return bool
     *
     * @codeCoverageIgnore
     *
     * @deprecated Use utils service instead
     */
    public function isPreviewMode()
    {
        return $this->utils->container()->isPreviewMode();
    }
}

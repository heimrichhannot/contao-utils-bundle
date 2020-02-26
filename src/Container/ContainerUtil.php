<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Container;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\System;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class ContainerUtil
{
    /** @var ContaoFramework */
    protected $framework;
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var FileLocator
     */
    private $fileLocator;
    /**
     * @var ScopeMatcher
     */
    private $scopeMatcher;

    public function __construct(ContainerInterface $container, FileLocator $fileLocator, ScopeMatcher $scopeMatcher)
    {
        $this->framework = $container->get('contao.framework');
        $this->fileLocator = $fileLocator;
        $this->scopeMatcher = $scopeMatcher;
        $this->container = $container;
    }

    /**
     * Returns the active bundles.
     */
    public function getActiveBundles(): array
    {
        return $this->container->getParameter('kernel.bundles');
    }

    /**
     * Checks if some bundle is active. Pass in the class name (e.g. 'HeimrichHannot\FilterBundle\HeimrichHannotContaoFilterBundle' or the legacy Contao 3 name like 'news').
     *
     * @return bool
     */
    public function isBundleActive(string $bundleName)
    {
        return \in_array($bundleName, array_merge(array_values($this->getActiveBundles()), array_keys($this->getActiveBundles())));
    }

    public function isBackend()
    {
        if ($request = $this->getCurrentRequest()) {
            return $this->scopeMatcher->isBackendRequest($request);
        }

        return false;
    }

    public function isFrontend()
    {
        if ($request = $this->getCurrentRequest()) {
            return $this->scopeMatcher->isFrontendRequest($request);
        }

        return false;
    }

    public function isFrontendCron()
    {
        return 'contao_frontend_cron' === $this->getCurrentRequest()->get('_route');
    }

    public function isInstall()
    {
        if ($request = $this->getCurrentRequest()) {
            return 'contao_install' === $request->get('_route');
        }

        return false;
    }

    public function isDev()
    {
        return 'dev' === System::getContainer()->getParameter('kernel.environment');
    }

    public function getCurrentRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }

    /**
     * @param string $category Use constants in ContaoContext
     */
    public function log(string $text, string $function, string $category)
    {
        $level = (ContaoContext::ERROR === $category ? LogLevel::ERROR : LogLevel::INFO);
        $logger = $this->container->get('monolog.logger.contao');

        $logger->log($level, $text, ['contao' => new ContaoContext($function, $category)]);
    }

    /**
     * Returns the project root path.
     *
     * @return mixed
     */
    public function getProjectDir()
    {
        return $this->container->getParameter('kernel.project_dir');
    }

    /**
     * Returns the web folder path.
     *
     * @return mixed
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
     */
    public function getBundlePath(string $bundleClass)
    {
        return $this->getBundleResourcePath($bundleClass, '', true);
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
     */
    public function getBundleResourcePath(string $bundleClass, string $ressourcePath = '', $first = false)
    {
        try {
            $className = (new \ReflectionClass($bundleClass))->getShortName();
        } catch (\ReflectionException $e) {
            return false;
        }
        $path = '@'.$className;
        $ressourcePath = ltrim($ressourcePath, '/');
        $path .= (empty($ressourcePath) ? '' : '/'.$ressourcePath);

        try {
            return $this->fileLocator->locate($path, null, $first);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Recursively merges a config.yml with a $extensionConfigs array in the context of ExtensionPluginInterface::getExtensionConfig().
     * Must be static, because on Plugin::getExtensionConfig() no contao.framework nor service huh.utils.container is available.
     *
     * @return array
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
}

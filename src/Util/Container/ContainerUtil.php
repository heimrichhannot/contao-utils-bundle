<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util\Container;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\System;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class ContainerUtil
{
    /** @var ContaoFramework */
    protected $framework;
    /**
     * @var array
     */
    protected $kernelBundles;
    /**
     * @var ContaoFramework
     */
    protected $contaoFramework;
    /**
     * @var RequestStack
     */
    protected $requestStack;
    /**
     * @var FileLocator
     */
    private $fileLocator;
    /**
     * @var ScopeMatcher
     */
    private $scopeMatcher;

    public function __construct(array $kernelBundles, ContaoFramework $framework, FileLocator $fileLocator, ScopeMatcher $scopeMatcher, RequestStack $requestStack)
    {
        $this->fileLocator = $fileLocator;
        $this->scopeMatcher = $scopeMatcher;
        $this->kernelBundles = $kernelBundles;
        $this->framework = $framework;
        $this->requestStack = $requestStack;
    }

    /**
     * Checks if some bundle is active. Pass in the class name (e.g. 'HeimrichHannot\FilterBundle\HeimrichHannotContaoFilterBundle' or the legacy Contao 3 name like 'news').
     */
    public function isBundleActive(string $bundleName): bool
    {
        return \in_array($bundleName, array_merge(array_values($this->kernelBundles), array_keys($this->kernelBundles)));
    }

    public function isBackend(): bool
    {
        if ($request = $this->requestStack->getCurrentRequest()) {
            return $this->scopeMatcher->isBackendRequest($request);
        }

        return false;
    }

    public function isFrontend(): bool
    {
        if ($request = $this->requestStack->getCurrentRequest()) {
            return $this->scopeMatcher->isFrontendRequest($request);
        }

        return false;
    }

    public function isFrontendCron(): bool
    {
        return $this->requestStack->getCurrentRequest() && 'contao_frontend_cron' === $this->requestStack->getCurrentRequest()->get('_route');
    }

    public function isInstall(): bool
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

    public function isMaintenanceModeActive()
    {
        return $this->container->get('lexik_maintenance.driver.factory')->getDriver()->isExists();
    }

    public function isPreviewMode()
    {
        return \defined('BE_USER_LOGGED_IN') && BE_USER_LOGGED_IN === true && \Input::cookie('FE_PREVIEW');
    }
}

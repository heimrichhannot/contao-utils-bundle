<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Container;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\System;
use Psr\Log\LogLevel;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class ContainerUtil
{
    /** @var ContaoFrameworkInterface */
    protected $framework;
    /**
     * @var FileLocator
     */
    private $fileLocator;
    /**
     * @var ScopeMatcher
     */
    private $scopeMatcher;

    public function __construct(ContaoFrameworkInterface $framework, FileLocator $fileLocator, ScopeMatcher $scopeMatcher)
    {
        $this->framework = $framework;
        $this->fileLocator = $fileLocator;
        $this->scopeMatcher = $scopeMatcher;
    }

    /**
     * Returns the active bundles.
     *
     * @return array
     */
    public function getActiveBundles(): array
    {
        return System::getContainer()->getParameter('kernel.bundles');
    }

    /**
     * Checks if some bundle is active. Pass in the class name (e.g. 'HeimrichHannot\FilterBundle\HeimrichHannotContaoFilterBundle' or the legacy Contao 3 name like 'news').
     *
     * @param string $bundleName
     *
     * @return bool
     */
    public function isBundleActive(string $bundleName)
    {
        return \in_array($bundleName, array_merge(array_values($this->getActiveBundles()), array_keys($this->getActiveBundles())), true);
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

    public function getCurrentRequest()
    {
        return System::getContainer()->get('request_stack')->getCurrentRequest();
    }

    /**
     * @param string $text
     * @param string $function
     * @param string $category Use constants in ContaoContext
     */
    public function log(string $text, string $function, string $category)
    {
        $level = (ContaoContext::ERROR === $category ? LogLevel::ERROR : LogLevel::INFO);
        $logger = System::getContainer()->get('monolog.logger.contao');

        $logger->log($level, $text, ['contao' => new ContaoContext($function, $category)]);
    }

    /**
     * Returns the project root path.
     *
     * @return mixed
     */
    public function getProjectDir()
    {
        return System::getContainer()->getParameter('kernel.project_dir');
    }

    /**
     * Returns the web folder path.
     *
     * @return mixed
     */
    public function getWebDir()
    {
        return System::getContainer()->getParameter('contao.web_dir');
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
     * @param string $activeExtensionName
     * @param string $extensionName
     * @param array  $extensionConfigs
     * @param string $configFile
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

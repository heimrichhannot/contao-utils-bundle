<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Container;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\System;
use Psr\Log\LogLevel;
use Symfony\Component\Yaml\Yaml;

class ContainerUtil
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
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
     * Checks if some bundle is active. Pass in the class name (e.g. 'HeimrichHannot\FilterBundle\HeimrichHannotContaoFilterBundle').
     *
     * @param string $bundleName
     *
     * @return bool
     */
    public function isBundleActive(string $bundleName)
    {
        return in_array($bundleName, $this->getActiveBundles(), true);
    }

    public function isBackend()
    {
        if ($request = $this->getCurrentRequest()) {
            return System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest($request);
        }

        return false;
    }

    public function isFrontend()
    {
        if ($request = $this->getCurrentRequest()) {
            return System::getContainer()->get('contao.routing.scope_matcher')->isFrontendRequest($request);
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

    public function getProjectDir()
    {
        return System::getContainer()->getParameter('kernel.project_dir');
    }

    public function getWebDir()
    {
        return System::getContainer()->getParameter('contao.web_dir');
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
        if ($activeExtensionName === $extensionName) {
            $config = Yaml::parseFile($configFile);

            $extensionConfigs = array_merge_recursive($extensionConfigs, $config);
        }

        return $extensionConfigs;
    }
}

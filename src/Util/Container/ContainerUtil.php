<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util\Container;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Input;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ContainerUtil implements ServiceSubscriberInterface
{
    /** @var ContaoFramework */
    protected $framework;
    /**
     * @var array
     */
    protected $kernelBundles;
    /**
     * @var RequestStack
     */
    protected $requestStack;
    /**
     * @var KernelInterface
     */
    protected $kernel;
    /**
     * @var ContainerInterface
     */
    protected $locator;
    /**
     * @var ScopeMatcher
     */
    private $scopeMatcher;

    public function __construct(ContainerInterface $locator, array $kernelBundles, KernelInterface $kernel, ContaoFrameworkInterface $framework, ScopeMatcher $scopeMatcher, RequestStack $requestStack)
    {
        $this->scopeMatcher = $scopeMatcher;
        $this->kernelBundles = $kernelBundles;
        $this->framework = $framework;
        $this->requestStack = $requestStack;
        $this->kernel = $kernel;
        $this->locator = $locator;
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
        if ($request = $this->requestStack->getCurrentRequest()) {
            return 'contao_install' === $request->get('_route');
        }

        return false;
    }

    public function isDev(): bool
    {
        return 'dev' === $this->kernel->getEnvironment();
    }

    /**
     * @param string $category Use constants in ContaoContext
     */
    public function log(string $text, string $function, string $category): void
    {
        $level = (ContaoContext::ERROR === $category ? LogLevel::ERROR : LogLevel::INFO);

        $this->locator->get('monolog.logger.contao')->log($level, $text, ['contao' => new ContaoContext($function, $category)]);
    }

    /**
     * Returns the path to the bundle in vendor folder
     * Attention: resolves symlinks!
     *
     * @param string $bundleClass The bundle class class constant (VendorMyBundle::class)
     *
     * @return string|null False on error
     */
    public function getBundlePath(string $bundleClass): ?string
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
     * @return string|array|null False on error
     */
    public function getBundleResourcePath(string $bundleClass, string $ressourcePath = '', $first = false)
    {
        try {
            $className = (new \ReflectionClass($bundleClass))->getShortName();
        } catch (\ReflectionException $e) {
            return null;
        }
        $path = '@'.$className;
        $ressourcePath = ltrim($ressourcePath, '/');
        $path .= (empty($ressourcePath) ? '' : '/'.$ressourcePath);

        try {
            return $this->locator->get(FileLocator::class)->locate($path, null, $first);
        } catch (FileLocatorFileNotFoundException $e) {
            return null;
        }
    }

    public function isMaintenanceModeActive(): bool
    {
        return $this->locator->get('lexik_maintenance.driver.factory')->getDriver()->isExists();
    }

    public function isPreviewMode(): bool
    {
        return \defined('BE_USER_LOGGED_IN') && BE_USER_LOGGED_IN === true && $this->framework->getAdapter(Input::class)->cookie('FE_PREVIEW');
    }

    public static function getSubscribedServices()
    {
        return [
            'lexik_maintenance.driver.factory',
            'monolog.logger.contao',
            FileLocator::class,
        ];
    }
}

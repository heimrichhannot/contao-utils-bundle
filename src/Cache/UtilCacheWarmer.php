<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Cache;

use Contao\CoreBundle\Config\ResourceFinderInterface;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Doctrine\DBAL\Connection;
use HeimrichHannot\UtilsBundle\Template\TemplateUtil;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class UtilCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ResourceFinderInterface
     */
    private $finder;

    /**
     * @var FileLocator
     */
    private $locator;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var TemplateUtil
     */
    private $templateUtil;

    /**
     * Constructor.
     *
     * @param string $rootDir
     */
    public function __construct(Filesystem $filesystem, ResourceFinderInterface $finder, FileLocator $locator, $rootDir, Connection $connection, TemplateUtil $templateUtil, ContaoFrameworkInterface $framework)
    {
        $this->filesystem = $filesystem;
        $this->finder = $finder;
        $this->locator = $locator;
        $this->rootDir = $rootDir;
        $this->connection = $connection;
        $this->templateUtil = $templateUtil;
        $this->framework = $framework;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        if (!$this->isCompleteInstallation()) {
            return;
        }

        $this->framework->initialize();

        $this->generateTemplateMapper($cacheDir);
        $this->generateDatabaseTreeCache($cacheDir);
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return true;
    }

    /**
     * Checks if the installation is complete.
     *
     * @return bool
     */
    private function isCompleteInstallation()
    {
        try {
            $this->connection->query('SELECT COUNT(*) FROM tl_page');
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Generates the template mapper array.
     *
     * @param string $cacheDir The cache directory
     */
    private function generateTemplateMapper($cacheDir)
    {
        $files = $this->templateUtil->getAllTemplates();

        if (empty($files)) {
            return;
        }

        $this->filesystem->dumpFile(
            $cacheDir.'/contao/config/twig-templates.php',
            sprintf("<?php\n\nreturn %s;\n", var_export($files, true))
        );
    }

    private function generateDatabaseTreeCache($cacheDir)
    {
        \Contao\System::getContainer()->get('huh.utils.cache.database_tree')->generateAllCacheTree($cacheDir);
    }
}

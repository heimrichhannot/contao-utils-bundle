<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Cache;

use Contao\CoreBundle\Framework\ContaoFramework;
use Doctrine\DBAL\Connection;
use HeimrichHannot\UtilsBundle\Template\TemplateLocator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class UtilCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var TemplateLocator
     */
    private $templateLocator;
    /**
     * @var DatabaseTreeCache
     */
    private $databaseTreeCache;
    /**
     * @var ContaoFramework
     */
    private $framework;

    /**
     * Constructor.
     *
     * @param string $rootDir
     */
    public function __construct(ContaoFramework $framework, Connection $connection, TemplateLocator $templateLocator, DatabaseTreeCache $databaseTreeCache)
    {
        $this->connection = $connection;
        $this->templateLocator = $templateLocator;
        $this->databaseTreeCache = $databaseTreeCache;
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
        $files = $this->templateLocator->getAllTemplates();

        if (empty($files)) {
            return;
        }

        $filesystem = new Filesystem();
        $filesystem->dumpFile(
            $cacheDir.'/contao/config/twig-templates.php',
            sprintf("<?php\n\nreturn %s;\n", var_export($files, true))
        );
    }

    private function generateDatabaseTreeCache($cacheDir)
    {
        $this->framework->initialize();
        $this->databaseTreeCache->generateAllCacheTree($cacheDir);
    }
}

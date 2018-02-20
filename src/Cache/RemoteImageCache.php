<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Cache;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\File;
use Contao\Validator;
use HeimrichHannot\Haste\Util\Curl;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RemoteImageCache
{
    /** @var ContaoFrameworkInterface */
    protected $framework;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContaoFrameworkInterface $framework, ContainerInterface $container)
    {
        $this->framework = $framework;
        $this->container = $container;
    }

    /**
     * Get a remote file from cache and cache file, if not already in cache.
     *
     * @param string $identifier Used as filename of the cached image. Should be unique within the folder scope
     * @param string $folder     Folder path or uuid of the file
     * @param $remoteUrl The url of the cached (or to cache) file
     * @param bool $returnUuid Return uuid instead of the path
     *
     * @return bool|string
     */
    public function get(string $identifier, $folder, $remoteUrl, $returnUuid = false)
    {
        $strFilename = $identifier.'.jpg';

        if (Validator::isUuid($folder)) {
            $objFolder = $this->container->get('huh.utils.file')->getFolderFromUuid($folder);
            $folder = $objFolder->value;
        }

        $objFile = new File(rtrim($folder, '/').'/'.$strFilename);

        if ($objFile->exists() && $objFile->size > 0) {
            return $returnUuid ? $objFile->getModel()->uuid : $objFile->path;
        }

        $strContent = $this->container->get('huh.utils.curl')->request($remoteUrl);

        if (!$strContent || !is_string($strContent)) {
            return false;
        }

        $objFile->write($strContent);
        $objFile->close();

        return $returnUuid ? $objFile->getModel()->uuid : $objFile->path;
    }
}

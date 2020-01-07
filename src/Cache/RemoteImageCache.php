<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Cache;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\File;
use Contao\Validator;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RemoteImageCache
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->framework = $container->get('contao.framework');
        $this->container = $container;
    }

    /**
     * Get a remote file from cache and cache file, if not already in cache.
     *
     * Returns false, if remote file could not be fetched or given uuid is not valid.
     * Else returns the url or, if $returnUuid is set true, the uuid of the image.
     *
     * @param string $identifier Used as filename of the cached image. Should be unique within the folder scope
     * @param string $folder     Folder path or uuid of the file
     * @param string $remoteUrl  The url of the cached (or to cache) file
     * @param bool   $returnUuid Return uuid instead of the path
     *
     * @throws \Exception
     *
     * @return bool|string
     */
    public function get(string $identifier, $folder, $remoteUrl, $returnUuid = false)
    {
        $strFilename = $identifier.'.jpg';

        if (Validator::isUuid($folder)) {
            $objFolder = $this->container->get('huh.utils.file')->getFolderFromUuid($folder);

            if (false === $objFolder) {
                return false;
            }
            $folder = $objFolder->value;
        }

        $objFile = new File(rtrim($folder, '/').'/'.$strFilename);

        if ($objFile->exists() && $objFile->size > 0) {
            return $returnUuid ? $objFile->getModel()->uuid : $objFile->path;
        }

        $strContent = $this->container->get('huh.utils.request.curl')->request($remoteUrl);

        if (!$strContent || !\is_string($strContent)) {
            return false;
        }

        $objFile->write($strContent);
        $objFile->close();

        return $returnUuid ? $objFile->getModel()->uuid : $objFile->path;
    }
}

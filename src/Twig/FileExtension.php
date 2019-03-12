<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FileExtension extends AbstractExtension implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    const FILE_OBJECT_PROPERTIES = [
        'size',
        'filesize',
        'name',
        'basename',
        'dirname',
        'filename',
        'extension',
        'origext',
        'tmpname',
        'path',
        'value',
        'mime',
        'hash',
        'ctime',
        'mtime',
        'atime',
        'icon',
        'dataUri',
        'imageSize',
        'width',
        'height',
        'imageViewSize',
        'viewWidth',
        'viewHeight',
        'isImage',
        'isGdImage',
        'isSvgImage',
        'channels',
        'bits',
        'isRgbImage',
        'isCmykImage',
        'handle',
    ];

    /**
     * Get list of twig filters.
     *
     * @return array|\Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('file_data', [$this, 'getFileData']),
            new TwigFilter('file_path', [$this, 'getFilePath']),
        ];
    }

    /**
     * Get file data based on given uuid.
     *
     * @param mixed $file                 File uuid
     * @param array $data                 Add file data here
     * @param array $jsonSerializeOptions Options for the object to array transformation
     *
     * @return array File data
     */
    public function getFileData($file, array $data = [], array $jsonSerializeOptions = []): array
    {
        if (null === ($fileObj = $this->container->get('huh.utils.file')->getFileFromUuid($file))) {
            return [];
        }

        $fileData = $this->container->get('huh.utils.class')->jsonSerialize($fileObj, $data, $jsonSerializeOptions);

        foreach (static::FILE_OBJECT_PROPERTIES as $property) {
            $fileData[$property] = $fileObj->{$property};
        }

        return $fileData;
    }

    /**
     * Get file path based on given uuid.
     *
     * @param mixed $file File uuid
     *
     * @return string File path
     */
    public function getFilePath($file): string
    {
        if (null === ($fileObj = $this->container->get('huh.utils.file')->getFileFromUuid($file))) {
            return '';
        }

        return $fileObj->path;
    }
}
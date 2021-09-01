<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Twig;

use Contao\File;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FileExtension extends AbstractExtension implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    const FILE_OBJECT_PROPERTIES = [
        'size',
        'readableFilesize',
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
        'ctime',
        'mtime',
        'atime',
        'icon',
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
            new TwigFilter('file_content', [$this, 'getFileContent']),
            new TwigFilter('bin2uuid', [$this, 'convertBinaryToUuid']),
        ];
    }

    public function convertBinaryToUuid($binary)
    {
        if (Validator::isBinaryUuid($binary)) {
            return StringUtil::binToUuid($binary);
        }

        return $binary;
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

    /**
     * Get file data based on given uuid.
     *
     * @param mixed $file                 File uuid
     * @param array $data                 Add file data here
     * @param array $jsonSerializeOptions Options for the object to array transformation
     *
     * @throws \ReflectionException
     *
     * @return array File data
     */
    public function getFileData($file, array $data = [], array $jsonSerializeOptions = []): array
    {
        if (null === ($fileObj = $this->container->get('huh.utils.file')->getFileFromUuid($file))) {
            return [];
        }

        $fileData = $this->container->get('huh.utils.class')->jsonSerialize($fileObj, $data, array_merge_recursive($jsonSerializeOptions, ['ignoreMethods' => true]));

        foreach (static::FILE_OBJECT_PROPERTIES as $property) {
            $fileData[$property] = $fileObj->{$property};
            $fileData['readableFilesize'] = System::getReadableSize($fileObj->filesize, 1);
        }

        $fileData['exists'] = $fileObj->exists();

        return array_merge($fileData, $data);
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

    public function getFileContent($file)
    {
        if (Validator::isUuid($file)) {
            /** @var File $fileObj */
            if (null === ($fileObj = $this->container->get('huh.utils.file')->getFileFromUuid($file))) {
                return '';
            }
        } elseif (\is_string($file)) {
            $file = str_replace($this->container->getParameter('kernel.project_dir').'/', '', $file);

            if (null === ($fileObj = new File($file)) || !$fileObj->exists()) {
                return '';
            }
        } else {
            return null;
        }

        return $fileObj->getContent();
    }
}

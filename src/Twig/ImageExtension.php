<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Twig;

use Contao\StringUtil;
use Contao\System;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ImageExtension extends AbstractExtension
{
    /**
     * Get list of twig filters.
     *
     * @return array|\Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('image', [$this, 'getImage']),
            new TwigFilter('image_caption', [$this, 'getImageCaption']),
            new TwigFilter('image_width', [$this, 'getImageWidth']),
        ];
    }

    /**
     * Get image based on given path/uuid.
     *
     * @param mixed  $image    File path/uuid
     * @param array  $size     Array containing [width, height, imageSize-ID]
     * @param array  $data     Add image data here [href => 'URL', class => 'img css class']…
     * @param string $template Use custom image template
     *
     * @return string Image html element with given size
     */
    public function getImage($image, array $size = null, array $data = [], string $template = '@HeimrichHannotContaoUtils/image.html.twig'): string
    {
        $data['image'] = $image;
        $data['size'] = $size;
        $imageData = [];
        System::getContainer()->get('huh.utils.image')->addToTemplateData('image', 'addImage', $imageData, $data);

        if (empty($imageData)) {
            return '';
        }

        try {
            return System::getContainer()->get('twig')->render($template, array_merge($imageData, $data));
        } catch (\Twig_Error $e) {
        }

        return '';
    }

    /**
     * Get image caption based on given path/uuid.
     *
     * @param mixed $image File path/uuid
     *
     * @return string|null Image caption if available, else null
     */
    public function getImageCaption($image): ?string
    {
        if (null === ($file = System::getContainer()->get('huh.utils.file')->getFileFromUuid($image))) {
            return null;
        }

        $meta = StringUtil::deserialize($file->getModel()->meta, true);

        if (!isset($meta[$GLOBALS['TL_LANGUAGE']]['caption'])) {
            return null;
        }

        return $meta[$GLOBALS['TL_LANGUAGE']]['caption'];
    }

    /**
     * Get image width based on given path/uuid.
     *
     * @param mixed $image File path/uuid
     *
     * @return int|null Image caption if available, else null
     */
    public function getImageWidth($image): ?string
    {
        if (null === ($file = System::getContainer()->get('huh.utils.file')->getFileFromUuid($image))) {
            return null;
        }

        return $file->width;
    }
}

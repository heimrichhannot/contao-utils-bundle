<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Twig;

use Contao\StringUtil;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ImageExtension extends AbstractExtension implements ContainerAwareInterface
{
    use ContainerAwareTrait;

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
            new TwigFilter('image_data', [$this, 'getImageData']),
            new TwigFilter('image_gallery', [$this, 'getImageGallery']),
            new TwigFilter('image_size', [$this, 'getImageSize']),
        ];
    }

    /**
     * Get image based on given path/uuid.
     *
     * @param mixed        $image    File path/uuid
     * @param string|array $size     Array or serialized string containing [width, height, imageSize-ID]
     * @param array        $data     Add image data here [href => 'URL', class => 'img css class']…
     * @param string       $template Use custom image template
     *
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @return string Image html element with given size
     */
    public function getImage($image, $size = null, array $data = [], string $template = 'image.html.twig'): string
    {
        $imageData = $this->getImageData($image, $size, $data);

        if (empty($imageData)) {
            return '';
        }

        return $this->container->get('huh.utils.template')->renderTwigTemplate($template, $imageData);
    }

    /**
     * Get image data based on given path/uuid.
     *
     * @param mixed        $image File path/uuid
     * @param string|array $size  Array or serialized string containing [width, height, imageSize-ID]
     * @param array        $data  Add image data here [href => 'URL', class => 'img css class']…
     *
     * @return array Image data
     */
    public function getImageData($image, $size = null, array $data = []): array
    {
        $data['image'] = $image;
        $data['size'] = \is_array($size) ? $size : StringUtil::deserialize($size, true);
        $imageData = [];
        $this->container->get('huh.utils.image')->addToTemplateData('image', 'addImage', $imageData, $data);

        if (empty($imageData)) {
            return [];
        }

        return array_merge($imageData, $data);
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
        if (null === ($file = $this->container->get('huh.utils.file')->getFileFromUuid($image))) {
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
     * @return string|null Image caption if available, else null
     */
    public function getImageWidth($image): ?string
    {
        if (null === ($file = $this->container->get('huh.utils.file')->getFileFromUuid($image))) {
            return null;
        }

        return $file->width;
    }

    public function getImageGallery($images, string $template = 'image_gallery.html.twig'): string
    {
        $galleryArray = \is_array($images) ? $images : StringUtil::deserialize($images, true);
        $galleryObjects = [];

        foreach ($galleryArray as $k => $v) {
            $galleryObjects['imageGallery'][$k] = $this->getImageData($v);
            $galleryObjects['imageGallery'][$k]['alt'] = $this->container->get('huh.utils.file')->getFileFromUuid($v)->name;
        }

        if (empty($galleryObjects)) {
            return [];
        }

        return $this->container->get('huh.utils.template')->renderTwigTemplate($template, $galleryObjects);
    }

    public function getImageSize($size)
    {
        $result = [];

        $size = \is_array($size) ? $size : StringUtil::deserialize($size, true);

        if (isset($size[2]) && $size[2] &&
            null !== ($imageSize = $this->container->get('huh.utils.model')->findModelInstanceByPk('tl_image_size', $size[2]))) {
            $result['width'] = $imageSize->width;
            $result['height'] = $imageSize->height;
        } else {
            $result['width'] = $size[0];
            $result['height'] = $size[1];
        }

        return $result;
    }
}

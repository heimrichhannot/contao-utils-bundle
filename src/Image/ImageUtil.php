<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Image;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\File;
use Contao\FilesModel;
use Contao\Frontend;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ImageUtil
{
    /**
     * @var ContaoFramework
     */
    protected $framework;
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->framework = $container->get('contao.framework');
        $this->container = $container;
    }

    /**
     * Add an image to a template.
     *
     * Advanced version of Controller::addImageToTemplate
     * with custom imageField and imageSelectorField and array instead of FrontendTemplate.
     *
     * @param string          $imageField         the image field name (typical singleSRC)
     * @param string          $imageSelectorField the image selector field indicated if an image is added (typical addImage)
     * @param array           $templateData       An array to add the generated data to
     * @param array           $item               The source data containing the imageField and imageSelectorField
     * @param int|null        $maxWidth           An optional maximum width of the image
     * @param string|null     $lightboxId         An optional lightbox ID
     * @param string|null     $lightboxName       An optional lightbox name
     * @param FilesModel|null $model              an optional file model used to read meta data
     */
    public function addToTemplateData(
        string $imageField,
        string $imageSelectorField,
        array &$templateData,
        array $item,
        int $maxWidth = null,
        string $lightboxId = null,
        string $lightboxName = null,
        FilesModel $model = null
    ) {
        $containerUtil = $this->container->get('huh.utils.container');
        $rootDir = $this->container->getParameter('kernel.project_dir');

        try {
            if (Validator::isUuid($item[$imageField])) {
                $file = $this->container->get('huh.utils.file')->getFileFromUuid($item[$imageField]);

                if (null === $file) {
                    return;
                }
            } else {
                $file = new File($item[$imageField]);
            }
            $imgSize = $file->imageSize;
        } catch (\Exception $e) {
            return;
        }

        if (null === $model) {
            $model = $file->getModel();
        }

        $size = StringUtil::deserialize($item['size']);

        if (is_numeric($size)) {
            $size = [0, 0, (int) $size];
        } elseif (!\is_array($size)) {
            $size = [];
        }

        $size += [0, 0, 'crop'];

        if (null === $maxWidth) {
            $maxWidth = ($containerUtil->isBackend()) ? 320 : Config::get('maxImageWidth');
        }

        $marginArray = ($containerUtil->isBackend()) ? '' : StringUtil::deserialize($item['imagemargin']);

        // Store the original dimensions
        $templateData['width'] = $imgSize[0];
        $templateData['height'] = $imgSize[1];

        // Adjust the image size
        if ($maxWidth > 0) {
            // Subtract the margins before deciding whether to resize (see #6018)
            if (\is_array($marginArray) && 'px' == $marginArray['unit']) {
                $margin = (int) $marginArray['left'] + (int) $marginArray['right'];

                // Reset the margin if it exceeds the maximum width (see #7245)
                if ($maxWidth - $margin < 1) {
                    $marginArray['left'] = '';
                    $marginArray['right'] = '';
                } else {
                    $maxWidth -= $margin;
                }
            }

            if ($size[0] > $maxWidth || (!$size[0] && !$size[1] && (!$imgSize[0] || $imgSize[0] > $maxWidth))) {
                // See #2268 (thanks to Thyon)
                $ratio = ($size[0] && $size[1]) ? $size[1] / $size[0] : (($imgSize[0] && $imgSize[1]) ? $imgSize[1] / $imgSize[0] : 0);

                $size[0] = $maxWidth;
                $size[1] = floor($maxWidth * $ratio);
            }
        }

        // Disable responsive images in the back end (see #7875)
        if ($containerUtil->isBackend()) {
            unset($size[2]);
        }

        $imageFile = $file;

        try {
            $src = $this->container->get('contao.image.image_factory')->create($rootDir.'/'.$file->path, $size)->getUrl($rootDir);
            $picture = $this->container->get('contao.image.picture_factory')->create($rootDir.'/'.$file->path, $size);

            $picture = [
                'img' => $picture->getImg($rootDir, TL_FILES_URL),
                'sources' => $picture->getSources($rootDir, TL_FILES_URL),
                'ratio' => '1.0',
                'copyright' => $file->getModel()->copyright,
            ];

            if ($src !== $file->path) {
                $imageFile = new File(rawurldecode($src));
            }
        } catch (\Exception $e) {
            $this->container->get('monolog.logger.contao')->log(
                LogLevel::ERROR,
                'Image "'.$file->path.'" could not be processed: '.$e->getMessage(),
                ['contao' => new ContaoContext(__METHOD__, TL_ERROR)]
            );

            $src = '';
            $picture = ['img' => ['src' => '', 'srcset' => ''], 'sources' => []];
        }

        // Image dimensions
        if (false !== ($imgSize = $imageFile->imageSize) && $imageFile->exists()) {
            $templateData['arrSize'] = $imgSize;
            $templateData['imgSize'] = ' width="'.$imgSize[0].'" height="'.$imgSize[1].'"';

            $picture['size'] = $imgSize;
            $picture['width'] = $imgSize[0];
            $picture['height'] = $imgSize[1];
            $picture['ratio'] = $imgSize[1] > 0 ? ($imgSize[0] / $imgSize[1]) : '1.0';
        }

        $meta = [];

        // Load the meta data
        if ($model instanceof FilesModel) {
            if ($containerUtil->isFrontend()) {
                global $objPage;

                $meta = Frontend::getMetaData($model->meta, $objPage->language);

                if (empty($meta) && null !== $objPage->rootFallbackLanguage) {
                    $meta = Frontend::getMetaData($model->meta, $objPage->rootFallbackLanguage);
                }
            } else {
                $meta = Frontend::getMetaData($model->meta, $GLOBALS['TL_LANGUAGE']);
            }

            $this->container->get('contao.framework')->getAdapter(Controller::class)->loadDataContainer('tl_files');

            // Add any missing fields
            foreach (array_keys($GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']) as $k) {
                if (!isset($meta[$k])) {
                    $meta[$k] = '';
                }
            }

            $meta['imageTitle'] = $meta['title'];
            $meta['imageUrl'] = $meta['link'];
            unset($meta['title'], $meta['link']);

            // Add the meta data to the item
            if (!$item['overwriteMeta']) {
                foreach ($meta as $k => $v) {
                    switch ($k) {
                        case 'alt':
                        case 'imageTitle':
                            $item[$k] = StringUtil::specialchars($v);

                            break;

                        default:
                            $item[$k] = $v;

                            break;
                    }
                }
            }
        }

        $picture['alt'] = StringUtil::specialchars($item['alt']);

        // Move the title to the link tag so it is shown in the lightbox
        if ($item['fullsize'] && $item['imageTitle'] && !$item['linkTitle']) {
            $item['linkTitle'] = $item['imageTitle'];
            unset($item['imageTitle']);
        }

        if (isset($item['imageTitle'])) {
            $picture['title'] = StringUtil::specialchars($item['imageTitle']);
        }

        $templateData['picture'] = $picture;

        // Provide an ID for single lightbox images in HTML5 (see #3742)
        if (null === $lightboxId && $item['fullsize']) {
            $lightboxId = 'lightbox['.substr(md5($lightboxName.'_'.$item['id']), 0, 6).']';
        }

        // Float image
        if ($item['floating']) {
            $templateData['floatClass'] = ' float_'.$item['floating'];
        }

        // Do not override the "href" key (see #6468)
        $hrefKey = ('' != $templateData['href']) ? 'imageHref' : 'href';

        // Image link
        if ($item['imageUrl'] && $containerUtil->isFrontend()) {
            $templateData[$hrefKey] = $item['imageUrl'];
            $templateData['attributes'] = '';

            if ($item['fullsize']) {
                // Open images in the lightbox
                if (preg_match('/\.(jpe?g|gif|png)$/', $item['imageUrl'])) {
                    // Do not add the TL_FILES_URL to external URLs (see #4923)
                    if (0 !== strncmp($item['imageUrl'], 'http://', 7) && 0 !== strncmp($item['imageUrl'], 'https://', 8)) {
                        $templateData[$hrefKey] = TL_FILES_URL.System::urlEncode($item['imageUrl']);
                    }

                    $templateData['attributes'] = ' data-lightbox="'.substr($lightboxId, 9, -1).'"';
                } else {
                    $templateData['attributes'] = ' target="_blank"';
                }
            }
        } // Fullsize view
        elseif ($item['fullsize'] && $containerUtil->isFrontend()) {
            $templateData[$hrefKey] = TL_FILES_URL.System::urlEncode($file->path);
            $templateData['attributes'] = ' data-lightbox="'.substr($lightboxId, 9, -1).'"';
        }

        // Add the meta data to the template
        foreach (array_keys($meta) as $k) {
            $templateData[$k] = $item[$k];
        }

        // Do not urlEncode() here because getImage() already does (see #3817)
        $templateData['src'] = TL_FILES_URL.$src;
        $templateData[$imageField] = $file->path;
        $templateData['linkTitle'] = $item['linkTitle'] ?: $item['title'];
        $templateData['fullsize'] = $item['fullsize'] ? true : false;
        $templateData['addBefore'] = ('below' != $item['floating']);
        $templateData['margin'] = Controller::generateMargin($marginArray);
        $templateData[$imageSelectorField] = true;

        // HOOK: modify image template data
        if (isset($GLOBALS['TL_HOOKS']['addImageToTemplateData']) && \is_array($GLOBALS['TL_HOOKS']['addImageToTemplateData'])) {
            foreach ($GLOBALS['TL_HOOKS']['addImageToTemplateData'] as $callback) {
                $templateData = System::importStatic($callback[0])->{$callback[1]}($templateData, $imageField, $imageSelectorField, $item, $maxWidth, $lightboxId, $lightboxName, $model);
            }
        }
    }

    /**
     * Convert sizes like 2em, 10cm or 12pt to pixels.
     *
     * @param string $size The size string
     *
     * @return int The pixel value
     */
    public function getPixelValue(string $size)
    {
        $value = preg_replace('/[^0-9.-]+/', '', $size);
        $unit = preg_replace('/[^acehimnprtvwx%]/', '', $size);

        // Convert 16px = 1em = 2ex = 12pt = 1pc = 1/6in = 2.54/6cm = 25.4/6mm = 100%
        switch ($unit) {
            case '':
            case 'px':
                return (int) round($value);

                break;

            case 'em':
                return (int) round($value * 16);

                break;

            case 'ex':
                return (int) round($value * 16 / 2);

                break;

            case 'pt':
                return (int) round($value * 16 / 12);

                break;

            case 'pc':
                return (int) round($value * 16);

                break;

            case 'in':
                return (int) round($value * 16 * 6);

                break;

            case 'cm':
                return (int) round($value * 16 / (2.54 / 6));

                break;

            case 'mm':
                return (int) round($value * 16 / (25.4 / 6));

                break;

            case '%':
                return (int) round($value * 16 / 100);

                break;
        }

        return 0;
    }
}

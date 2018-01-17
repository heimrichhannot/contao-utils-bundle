<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\UtilsBundle\Image;

use Contao\Config;
use Contao\Controller;
use Contao\File;
use Contao\FilesModel;
use Contao\Frontend;
use Contao\StringUtil;
use Contao\System;

class Image
{
    public static function addToTemplateData(array &$templateData, array $item, int $maxWidth = null, string $lightboxId = null,
        string $lightboxName = null, FilesModel $model = null)
    {
        $containerUtil = System::getContainer()->get('huh.utils.container');

        try {
            $file = new File($item['singleSRC']);
        } catch (\Exception $e) {
            $file = new \stdClass();
            $file->imageSize = false;
        }

        $imgSize = $file->imageSize;
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

        $marginArray = ($containerUtil->isBackend()) ? [] : StringUtil::deserialize($item['imagemargin']);

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

        try {
            $src =
                System::getContainer()->get('contao.image.image_factory')->create(TL_ROOT.'/'.$item['singleSRC'], $size)->getUrl(TL_ROOT);
            $picture = System::getContainer()->get('contao.image.picture_factory')->create(TL_ROOT.'/'.$item['singleSRC'], $size);

            $picture = [
                'img' => $picture->getImg(TL_ROOT, TL_FILES_URL),
                'sources' => $picture->getSources(TL_ROOT, TL_FILES_URL),
            ];

            if ($src !== $item['singleSRC']) {
                $file = new File(rawurldecode($src));
            }
        } catch (\Exception $e) {
            System::log('Image "'.$item['singleSRC'].'" could not be processed: '.$e->getMessage(), __METHOD__, TL_ERROR);

            $src = '';
            $picture = ['img' => ['src' => '', 'srcset' => ''], 'sources' => []];
        }

        // Image dimensions
        if ($file->exists() && false !== ($imgSize = $file->imageSize)) {
            $templateData['arrSize'] = $imgSize;
            $templateData['imgSize'] = ' width="'.$imgSize[0].'" height="'.$imgSize[1].'"';
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

            Controller::loadDataContainer('tl_files');

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
        }

        // Fullsize view
        elseif ($item['fullsize'] && $containerUtil->isFrontend()) {
            $templateData[$hrefKey] = TL_FILES_URL.System::urlEncode($item['singleSRC']);
            $templateData['attributes'] = ' data-lightbox="'.substr($lightboxId, 9, -1).'"';
        }

        // Add the meta data to the template
        foreach (array_keys($meta) as $k) {
            $templateData[$k] = $item[$k];
        }

        // Do not urlEncode() here because getImage() already does (see #3817)
        $templateData['src'] = TL_FILES_URL.$src;
        $templateData['singleSRC'] = $item['singleSRC'];
        $templateData['linkTitle'] = $item['linkTitle'] ?: $item['title'];
        $templateData['fullsize'] = $item['fullsize'] ? true : false;
        $templateData['addBefore'] = ('below' != $item['floating']);
        $templateData['margin'] = Controller::generateMargin($marginArray);
        $templateData['addImage'] = true;
    }
}

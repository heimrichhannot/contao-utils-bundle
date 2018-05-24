<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Pdf;

use HeimrichHannot\UtilsBundle\Cache\FileCache;
use Spatie\PdfToImage\Pdf;

class PdfPreview
{
    /**
     * @var FileCache
     */
    private $cache;
    /**
     * @var string
     */
    private $webDir;

    public function __construct(FileCache $cache, string $webDir)
    {
        $this->cache = $cache;
        $this->cache->setNamespace('pdfPreview');
        $this->webDir = $webDir.'/..';
    }

    /**
     * @param string $pdfPath       The path to the pdf file
     * @param array  $options       Additional rendering options. See generatePdfPreview
     * @param string $fileExtension
     *
     * @return string
     */
    public function getCachedPdfPreview(string $pdfPath, array $options = [], string $fileExtension = 'jpg')
    {
        $pdfCache = $this;
        $imagePath = $this->cache->get($pdfPath, function ($pdfPath, $cachePath, $cacheFileName) use ($pdfCache, $options, $fileExtension) {
            return $pdfCache->generatePdfPreview($pdfPath, $cachePath.'/'.$cacheFileName.'.'.$fileExtension, $options);
        });

        return $imagePath ? $imagePath.'.'.$fileExtension : null;
    }

    /**
     * Generate a image preview of the given pdf.
     *
     * Possible file extension: jpg, jpeg, png
     *
     * Additional options:
     * int page The page to render (default: 1)
     * int compressionQuality Pdf compression quality (default: null)
     * int resolution Raster resolution (default: 144)
     *
     * @param string $pdfPath   the path to the pdf file
     * @param string $imagePath the path where the image file should be saved (including file name and extension)
     * @param array  $options   Additional rendering options
     *
     * @return bool
     */
    public function generatePdfPreview(string $pdfPath, string $imagePath, array $options = [])
    {
        $imageExtension = pathinfo($imagePath, PATHINFO_EXTENSION);
        try {
            $pdf = new Pdf($this->webDir.'/'.$pdfPath);
            if (isset($option['page']) && $options['page'] > 0) {
                $pdf->setPage($options['page']);
            }
            if (isset($option['compressionQuality']) && $options['compressionQuality'] > 0) {
                $pdf->setCompressionQuality($options['compressionQuality']);
            }
            if (isset($option['resolution']) && $options['resolution'] > 0) {
                $pdf->setResolution($options['resolution']);
            }
            if (!empty($imageExtension)) {
                $pdf->setOutputFormat($imageExtension);
            }
            $pdf->saveImage($this->webDir.'/'.$imagePath);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}

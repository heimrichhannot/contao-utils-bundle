<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Pdf;

use Ghostscript\Transcoder;
use HeimrichHannot\UtilsBundle\Cache\FileCache;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use Spatie\PdfToImage\Pdf;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

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
    /**
     * @var ContainerUtil
     */
    private $containerUtil;

    public function __construct(FileCache $cache, ContainerUtil $containerUtil, string $webDir)
    {
        $this->cache = $cache;
        $this->cache->setNamespace('pdfPreview');
        $this->webDir = $webDir.'/..';
        $this->containerUtil = $containerUtil;
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
        $imagePath = $this->cache->get($pdfPath, $fileExtension, function ($pdfPath, $cachePath, $cacheFileName) use ($pdfCache, $options) {
            return $pdfCache->generatePdfPreview($pdfPath, $cachePath.'/'.$cacheFileName, $options);
        });

        return $imagePath ? $imagePath : null;
    }

    /**
     * Generate a image preview of the given pdf.
     *
     * Possible PdfTranscoder: spatie (spatie/pdf-to-image), alchemy (alchemy/ghostscript)
     *
     * Possible file extensions: jpg, jpeg, png
     *
     * Additional options:
     * - string pdfTranscoder The pdf transcoder to use (default: spatie)
     * - int page The page to render (default: 1)
     * - int compressionQuality Pdf compression quality (default: null) (spatie only)
     * - int resolution Raster resolution (default: 144)(spatie only)
     * - bool absolutePdfPath Set true if pdf path is absolute (default: false)
     * - bool absoluteImagePath Set true if image path is absolute (default: false)
     *
     * @param string $pdfPath   the relative path to the pdf file
     * @param string $imagePath the relative path where the image file should be saved (including file name and extension)
     * @param array  $options   Additional rendering options
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function generatePdfPreview(string $pdfPath, string $imagePath, array $options = [])
    {
        if (!isset($options['absolutePdfPath']) || true !== $options['absolutePdfPath']) {
            $pdfPath = $this->webDir.'/'.$pdfPath;
        }

        if (!isset($options['absoluteImagePath']) || true !== $options['absoluteImagePath']) {
            $imagePath = $this->webDir.'/'.$imagePath;
        }
        $pdfTranscoder = isset($options['pdfTranscoder']) ? $options['pdfTranscoder'] : '';

        switch ($pdfTranscoder) {
            case 'alchemy':
                return $this->alchemyPdf($pdfPath, $imagePath, $options);

            case 'spatie':
            default:
                return $this->spatiePdf($pdfPath, $imagePath, $options);
        }
    }

    /**
     * @param string $pdfPath
     * @param string $imagePath
     *
     * @throws \Exception
     *
     * @return bool
     */
    protected function spatiePdf(string $pdfPath, string $imagePath, array $options = [])
    {
        try {
            $this->containerUtil->isBundleActive('spatie/pdf-to-image');
        } catch (\Exception $e) {
            throw new \Exception('Package spatie/pdf-to-image is not installed. Please install or use another pdf ttranscoder.');
        }
        $imageExtension = pathinfo($imagePath, PATHINFO_EXTENSION);

        try {
            $pdf = new Pdf($pdfPath);

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
            $pdf->saveImage($imagePath);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param string $pdfPath
     * @param string $imagePath
     *
     * @throws \Exception
     *
     * @return bool
     */
    protected function alchemyPdf(string $pdfPath, string $imagePath, array $options = [])
    {
        try {
            $this->containerUtil->isBundleActive('alchemy/ghostscript');
        } catch (\Exception $e) {
            throw new \Exception('Package alchemy/ghostscript is not installed. Please install or use another pdf transcoder.');
        }
        $imageExtension = pathinfo($imagePath, PATHINFO_EXTENSION);
        $allowedExtensions = ['jpg', 'jpeg', 'png'];

        if (!\in_array($imageExtension, $allowedExtensions)) {
            throw new InvalidTypeException('Only one of the following file types is allowed: '.implode(
                ', ', $allowedExtensions)
            );
        }

        if ('jpg' === $imageExtension) {
            $imageExtension = 'jpeg';
        }
        $command = [
            '-sDEVICE='.$imageExtension,
            '-dNOPAUSE',
            '-dBATCH',
            '-dSAFER',
            '-sOutputFile='.$imagePath,
        ];

        if (isset($option['page']) && \is_int($options['page']) && $options['page'] > 0) {
            $command[] = sprintf('-dFirstPage=%d', $options['page']);
            $command[] = sprintf('-dLastPage=%d', $options['page']);
        }

        try {
            $command[] = $pdfPath;
            $transcoder = Transcoder::create();
            $transcoder->command($command);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}

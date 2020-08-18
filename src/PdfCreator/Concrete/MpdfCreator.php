<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\PdfCreator\Concrete;

use HeimrichHannot\UtilsBundle\PdfCreator\AbstractPdfCreator;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class MpdfCreator extends AbstractPdfCreator
{
    /**
     * @var array
     */
    protected $legacyFontDirectoryConfig;

    /**
     * MpdfCreator constructor.
     */
    public function __construct()
    {
        if (!class_exists('Mpdf\Mpdf')) {
            throw new \Exception('The mPDF library could not be found and is required by this service. Please install it with "composer require mpdf/mpdf ^8.0".');
        }

        if (version_compare(Mpdf::VERSION, '7.0') < 0 || version_compare(Mpdf::VERSION, 9) >= 0) {
            throw new \Exception('Only mPDF library versions 7.x and 8.x are supported.');
        }
    }

    public function render(): void
    {
        $config = [];

        if ($this->getMediaType()) {
            $config['CSSselectMedia'] = $this->getMediaType();
        }

        $config = $this->applyDocumentFormatConfiguration($config);

        $config = $this->applyFonts($config);

        $pdf = new Mpdf($config);

        $this->applyTemplate($pdf);

        if ($this->getHtmlContent()) {
            $pdf->WriteHTML($this->getHtmlContent());
        }

        $outputMode = '';
        $filename = $this->getFilename() ?: '';

        switch ($this->getOutputMode()) {
            case static::OUTPUT_MODE_STRING:
                $outputMode = Destination::STRING_RETURN;

                break;

            case static::OUTPUT_MODE_FILE:
                if ($folder = $this->getFolder() && $this->getFilename()) {
                    $filename = rtrim($folder, '/').'/'.$filename;
                }

                $outputMode = Destination::FILE;

                break;

            case static::OUTPUT_MODE_DOWNLOAD:
                $outputMode = Destination::DOWNLOAD;

                break;

            case static::OUTPUT_MODE_INLINE:
                $outputMode = Destination::INLINE;

                break;
        }

        $pdf->Output($filename, $outputMode);
    }

    public function getSupportedOutputModes(): array
    {
        return static::OUTPUT_MODES;
    }

    public static function getType(): string
    {
        return 'mpdf';
    }

    /**
     * Add font directories to the config. Directory must contain mpdf-config.php.
     * Fallback method for legacy implementation, will be removed in a future version.
     *
     * @param array $paths Absolute path to font dir
     *
     * @return self Current pdf creator instance
     *
     * @deprecated Use addFont instead
     */
    public function addFontDirectories(array $paths): self
    {
        $defaultConfig = (new  ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        foreach ($paths as $fontDir) {
            if (!file_exists($fontDir) || !file_exists($fontDir.\DIRECTORY_SEPARATOR.'mpdf-config.php')) {
                continue;
            }

            $configPath = $fontDir.\DIRECTORY_SEPARATOR.'mpdf-config.php';
            $fontConfig = require_once $configPath;

            if (!\is_array($fontConfig)) {
                continue;
            }

            if (!isset($fontConfig['fontDir'])) {
                $fontConfig['fontDir'] = array_merge($fontDirs, [
                    $fontDir,
                ]);
            }

            $this->legacyFontDirectoryConfig = array_merge($this->legacyFontDirectoryConfig ?: [], $fontConfig);
        }

        return $this;
    }

    protected function applyFonts(array $config): array
    {
        if ($this->getFonts()) {
            if ($this->legacyFontDirectoryConfig) {
                $fontDirs = $this->legacyFontDirectoryConfig['fontDir'];
            } else {
                $defaultConfig = (new ConfigVariables())->getDefaults();
                $fontDirs = $defaultConfig['fontDir'];
            }

            if ($this->legacyFontDirectoryConfig) {
                $fontData = $this->legacyFontDirectoryConfig['fontdata'];
            } else {
                $defaultFontConfig = (new FontVariables())->getDefaults();
                $fontData = $defaultFontConfig['fontdata'];
            }

            $dirs = [];
            $families = [];

            foreach ($this->getFonts() as $font) {
                $file = pathinfo($font['filepath']);
                $dirs[] = $file['dirname'];
                $fontStyle = 'R';

                switch ($font['style']) {
                    case static::FONT_STYLE_REGUALAR:
                        $fontStyle = 'R';

                        break;

                    case static::FONT_STYLE_BOLD:
                        $fontStyle = 'B';

                        break;

                    case static::FONT_STYLE_ITALIC:
                        $fontStyle = 'I';

                        break;

                    case static::FONT_STYLE_BOLDITALIC:
                        $fontStyle = 'BI';

                        break;
                }
                $families[$font['family']][$fontStyle] = $file['basename'];
            }

            $config['fontDir'] = array_merge($fontDirs, array_unique($dirs));
            $config['fontdata'] = array_merge($fontData, $families);
        }

        return $config;
    }

    protected function applyDocumentFormatConfiguration(array $config): array
    {
        if ($this->getMargins()) {
            if ($this->getMargins()['top']) {
                $config['margin_top'] = $this->getMargins()['top'];
            }

            if ($this->getMargins()['right']) {
                $config['margin_right'] = $this->getMargins()['right'];
            }

            if ($this->getMargins()['bottom']) {
                $config['margin_bottom'] = $this->getMargins()['bottom'];
            }

            if ($this->getMargins()['left']) {
                $config['margin_left'] = $this->getMargins()['left'];
            }
        }

        if ($this->getOrientation()) {
            switch ($this->getOrientation()) {
                case static::ORIENTATION_PORTRAIT:
                    $config['orientation'] = 'P';

                    break;

                case static::ORIENTATION_LANDSCAPE:
                    $config['orientation'] = 'L';

                    break;
            }
        }

        if ($this->getFormat()) {
            if (\is_string($this->getFormat()) && static::ORIENTATION_LANDSCAPE === $this->getOrientation()) {
                $config['format'] = $this->getFormat().'-L';
            } else {
                $config['format'] = $this->getFormat();
            }
        }

        return $config;
    }

    /**
     * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @throws \setasign\Fpdi\PdfParser\PdfParserException
     * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
     */
    protected function applyTemplate(Mpdf $pdf): void
    {
        if ($this->getTemplateFilePath()) {
            if (file_exists($this->getTemplateFilePath())) {
                if (version_compare(Mpdf::VERSION, '8', '>')) {
                    $pageCount = $pdf->setSourceFile($this->getTemplateFilePath());
                    $tplIdx = $pdf->importPage($pageCount);
                    $pdf->useTemplate($tplIdx);
                } else {
                    // mpdf 7.x support
                    $pdf->SetImportUse();
                    $pageCount = $pdf->SetSourceFile($this->getTemplateFilePath());
                    $tplIdx = $pdf->ImportPage($pageCount);
                    $pdf->UseTemplate($tplIdx);
                }
            } else {
                trigger_error('Pdf template does not exist.', E_USER_NOTICE);
            }
        }
    }
}

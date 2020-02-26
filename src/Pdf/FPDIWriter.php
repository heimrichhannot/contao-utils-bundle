<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Pdf;

use Contao\System;
use setasign\Fpdi\Tcpdf\Fpdi;

class FPDIWriter extends AbstractPdfWriter
{
    /**
     * Current fpdi instance.
     *
     * @var Fpdi
     */
    protected $pdf;

    /**
     * Master pdf template.
     *
     * @var string
     */
    protected $template;

    /**
     * TCPDFWriter constructor.
     */
    public function __construct()
    {
        if (!class_exists('setasign\Fpdi\Tcpdf\Fpdi')) {
            throw new \Exception('The FPDI library could not be found and is required by this service. Please install it via "composer require setasign/fpdi-tcpdf ^2.2".');
        }

        parent::__construct();
    }

    public function setDefaultConfig()
    {
        $this->config = [
            'encoding' => \Config::get('characterSet'),
            'format' => 'A4',
            'orientation' => 'P',
            'unit' => 'mm',
        ];
    }

    /**
     * Get the master template path.
     *
     * @return string
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * Set the master template path.
     *
     * @return PdfWriter Current pdf writer instance
     */
    public function setTemplate(string $template): self
    {
        $projectDir = System::getContainer()->get('huh.utils.container')->getProjectDir();

        $this->template = $projectDir.\DIRECTORY_SEPARATOR.ltrim(preg_replace('#^'.$projectDir.'#', '', $template), \DIRECTORY_SEPARATOR);

        return $this;
    }

    /**
     * Prepare the current fpdi object.
     */
    public function prepare(): Fpdi
    {
        $this->pdf = $this->getPdf();

        // set the custom pdf template
        if (null !== $this->getTemplate() && file_exists($this->getTemplate())) {
            $pageCount = $this->pdf->setSourceFile($this->getTemplate());

            $tplIdx = $this->pdf->importPage($pageCount);
            $this->pdf->fpdiUseImportedPage($tplIdx);
        }

        $this->pdf->writeHTML($this->html);

        $this->isPrepared = true;

        return $this->pdf;
    }

    /**
     * @param string $mode
     */
    public function generate($mode = self::OUTPUT_MODE_DOWNLOAD): void
    {
        if (null === $this->pdf || !$this->isPrepared()) {
            $this->prepare();
        }

        $outputMode = '';
        $filename = $this > $this->getFileName();

        switch ($mode) {
            case static::OUTPUT_MODE_DOWNLOAD:
                $outputMode = 'D';

                break;

            case static::OUTPUT_MODE_FILE:
                if ($folder = $this->getFolder()) {
                    $filename = rtrim($folder, '/').'/'.$this->getFileName();
                }

                $outputMode = 'F';

                break;

            case static::OUTPUT_MODE_INLINE:
                $outputMode = 'I';

                break;
        }

        $this->pdf->Output($filename, $outputMode);
    }

    /**
     * Get current pdf object.
     *
     * @param bool $init Set true if you want to create a new pdf regardless there is always an existing pdf
     *
     * @return
     */
    public function getPdf(bool $init = false): Fpdi
    {
        if (null === $this->pdf || true === $init) {
            $this->pdf = new Fpdi(
                $this->config['orientation'],
                $this->config['unit'],
                $this->config['format'],
                true,
                $this->config['encoding']
            );

            $this->pdf->setCellMargins(
                $this->config['margins']['left'],
                $this->config['margins']['top'],
                $this->config['margins']['right'],
                $this->config['margins']['bottom']
            );

            // avoid having black borders in header and footer (see https://stackoverflow.com/a/17172044/1463757)
            $this->pdf->SetPrintHeader(false);
            $this->pdf->SetPrintFooter(false);

            $this->pdf->AddPage();
        }

        return $this->pdf;
    }

    /**
     * @param $family
     * @param $weight
     * @param $filename string The absolute filename including path
     */
    public function addFont($family, $weight, $filename)
    {
        $projectDir = System::getContainer()->get('huh.utils.container')->getProjectDir();
        $tcpdfDir = $projectDir.'/vendor/tecnickcom/tcpdf';

        // add font to tcpdf
        $fontParts = pathinfo($filename);

        $fontname = strtolower($fontParts['filename']);
        $fontname = preg_replace('/[^a-z0-9_]/', '', $fontname);

        $definitionFile = $tcpdfDir.'/fonts/'.$fontname.'.php';

        if (!file_exists($definitionFile)) {
            \TCPDF_FONTS::addTTFfont($filename);
        }

        if (!file_exists($definitionFile)) {
            throw new \Exception('The font "'.$filename.'" couldn\'t be added to TCPDF\'s font dir.');
        }

        // add font to pdf document
        $pdf = $this->getPdf();

        $pdf->AddFont($family, $weight, $definitionFile);
    }

    /**
     * Check if prepare was already triggered.
     */
    public function isPrepared(): bool
    {
        return $this->isPrepared;
    }
}

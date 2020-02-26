<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Pdf;

use Contao\System;
use Mpdf\Config\ConfigVariables;
use Mpdf\Mpdf;

class PdfWriter extends AbstractPdfWriter
{
    /**
     * Current mpdf instance.
     *
     * @var Mpdf
     */
    protected $pdf;

    /**
     * Master pdf template.
     *
     * @var string
     */
    protected $template;

    /**
     * constructor.
     */
    public function __construct()
    {
        if (!class_exists('Mpdf\Mpdf')) {
            throw new \Exception('The mPDF library could not be found and is required by this service. Please install it via "composer require mpdf/mpdf ^7.0".');
        }

        parent::__construct();
    }

    public function setDefaultConfig()
    {
        $this->config = [
            'mode' => \Config::get('characterSet'),
            'format' => 'A4',
            'orientation' => 'P',
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
     * Prepare the current mpdf object.
     */
    public function prepare(): Mpdf
    {
        $this->pdf = $this->getPdf();

        // set the custom pdf template
        if (null !== $this->getTemplate() && file_exists($this->getTemplate())) {
            $this->pdf->SetImportUse();

            $pageCount = $this->pdf->SetSourceFile($this->getTemplate());
            $tplIdx = $this->pdf->ImportPage($pageCount);
            $this->pdf->UseTemplate($tplIdx);
            $this->pdf->UseTemplate($tplIdx);
        }

        $this->pdf->WriteHTML($this->html);

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
        $filename = $this->getFileName();

        switch ($mode) {
            case static::OUTPUT_MODE_DOWNLOAD:
                $outputMode = 'D';

                break;

            case static::OUTPUT_MODE_FILE:
                if ($folder = $this->getFolder()) {
                    $projectDir = System::getContainer()->get('huh.utils.container')->getProjectDir();
                    $filename = $projectDir.'/'.rtrim($folder, '/').'/'.$filename;
                }

                $outputMode = 'F';

                break;

            case static::OUTPUT_MODE_INLINE:
                $outputMode = 'I';

                break;
        }

        $this->pdf->output($filename, $outputMode);
    }

    /**
     * Add font directories to the config.
     *
     * @param array $paths Directory pathseader
     *
     * @return PdfWriter Current pdf writer instance
     */
    public function addFontDirectories(array $paths): self
    {
        $projectDir = System::getContainer()->get('huh.utils.container')->getProjectDir();

        $defaultConfig = (new  ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        foreach ($paths as $path) {
            $fontDir = $projectDir.\DIRECTORY_SEPARATOR.ltrim($path, \DIRECTORY_SEPARATOR);

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

            $this->config = array_merge($this->config, $fontConfig);
        }

        return $this;
    }

    /**
     * Get current pdf object.
     *
     * @param bool $init Set true if you want to create a new pdf regardless there is always an existing pdf
     */
    public function getPdf(bool $init = false): Mpdf
    {
        $this->pdf = (null === $this->pdf || true === $init) ? new Mpdf($this->config) : $this->pdf;

        return $this->pdf;
    }
}

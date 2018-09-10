<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Pdf;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use Mpdf\Config\ConfigVariables;
use Mpdf\Mpdf;

class PdfWriter
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    /**
     * Current mpdf instance.
     *
     * @var Mpdf
     */
    protected $pdf;

    /**
     * Pdf html content including styles.
     *
     * @var string
     */
    protected $html;

    /**
     * Pdf file name.
     *
     * @var string
     */
    protected $fileName;

    /**
     * Pdf configuration.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Master pdf template.
     *
     * @var string
     */
    protected $template;

    /**
     * @var bool
     */
    protected $isPrepared = false;

    /**
     * PdfWriter constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;

        if (!class_exists('Mpdf\Mpdf')) {
            throw new \Exception('The mPDF library could not be found and is required by this service. Please install it via "composer require mpdf/mpdf ^7.0".');
        }

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
     * @param string $template
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
     *
     * @return Mpdf
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
     * Generate the pdf.
     *
     * @param bool $download Set false if the pdf should not be downloaded
     */
    public function generate(bool $download = true): void
    {
        if (null === $this->pdf || !$this->isPrepared()) {
            $this->prepare();
        }

        $this->pdf->output($this->getFileName(), true === $download ? 'D' : '');
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
     * Get html including styles.
     *
     * @return string
     */
    public function getHtml(): ?string
    {
        return $this->html;
    }

    /**
     * Set html including styles.
     *
     * @param string $html
     *
     * @return PdfWriter
     */
    public function setHtml(string $html): self
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Get current pdf object.
     *
     * @param bool $init Set true if you want to create a new pdf regardless there is always an existing pdf
     *
     * @return Mpdf
     */
    public function getPdf(bool $init = false): Mpdf
    {
        $this->pdf = (null === $this->pdf || true === $init) ? new Mpdf($this->config) : $this->pdf;

        return $this->pdf;
    }

    /**
     * Get the pdf file name.
     *
     * @return string
     */
    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    /**
     * Set the pdf filename.
     *
     * @param string $fileName
     *
     * @return PdfWriter Current pdf writer instance
     */
    public function setFileName(string $fileName): self
    {
        if (!preg_match('#.pdf$#i', $fileName)) {
            $fileName .= '.pdf';
        }

        $this->fileName = System::getContainer()->get('huh.utils.file')->sanitizeFileName($fileName);

        return $this;
    }

    /**
     * Get the pdf config.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set pdf config, replace default with custom config.
     *
     * @param array $config
     *
     * @return PdfWriter Current pdf writer instance
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Merge current pdf config with given.
     *
     * @param array $config
     *
     * @return PdfWriter Current pdf writer instance
     */
    public function mergeConfig(array $config): self
    {
        $this->config = array_merge($this->config, $config);

        return $this;
    }

    /**
     * Check if prepare was already triggered.
     *
     * @return bool
     */
    public function isPrepared(): bool
    {
        return $this->isPrepared;
    }

    /**
     * @return ContaoFrameworkInterface
     */
    public function getFramework(): ContaoFrameworkInterface
    {
        return $this->framework;
    }
}

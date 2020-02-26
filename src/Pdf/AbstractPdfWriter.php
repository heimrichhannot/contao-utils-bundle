<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Pdf;

use Contao\System;

abstract class AbstractPdfWriter
{
    const OUTPUT_MODE_DOWNLOAD = 'download';
    const OUTPUT_MODE_FILE = 'file';
    const OUTPUT_MODE_INLINE = 'inline';

    const OUTPUT_MODES = [
        self::OUTPUT_MODE_DOWNLOAD,
        self::OUTPUT_MODE_FILE,
        self::OUTPUT_MODE_INLINE,
    ];

    /**
     * @var mixed The pdf object
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
     * The folder in case of saving to file.
     *
     * @var string
     */
    protected $folder;

    /**
     * Pdf configuration.
     *
     * @var array
     */
    protected $config = [];

    /**
     * @var bool
     */
    protected $isPrepared = false;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->setDefaultConfig();
    }

    abstract public function setDefaultConfig();

    /**
     * Prepare the current object.
     */
    public function prepare()
    {
        $this->pdf = $this->getPdf();

        $this->pdf->writeHTML($this->html);

        $this->isPrepared = true;

        return $this->pdf;
    }

    /**
     * Generate the pdf.
     *
     * @param string $mode
     */
    abstract public function generate($mode = self::OUTPUT_MODE_DOWNLOAD): void;

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
     */
    abstract public function getPdf(bool $init = false);

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
     * @return PdfWriter Current pdf writer instance
     */
    public function setFileName(string $fileName): self
    {
        if (!preg_match('#\.pdf$#i', $fileName)) {
            $fileName .= '.pdf';
        }

        $this->fileName = System::getContainer()->get('huh.utils.file')->sanitizeFileName($fileName);

        return $this;
    }

    /**
     * Get the pdf config.
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set pdf config, replace default with custom config.
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
     * @return PdfWriter Current pdf writer instance
     */
    public function mergeConfig(array $config): self
    {
        $this->config = array_merge($this->config, $config);

        return $this;
    }

    /**
     * Check if prepare was already triggered.
     */
    public function isPrepared(): bool
    {
        return $this->isPrepared;
    }

    /**
     * @return string
     */
    public function getFolder(): ?string
    {
        return $this->folder;
    }

    public function setFolder(string $folder): void
    {
        $this->folder = $folder;
    }
}

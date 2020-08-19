<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\PdfCreator;

abstract class AbstractPdfCreator
{
    const OUTPUT_MODE_DOWNLOAD = 'download';
    const OUTPUT_MODE_FILE = 'file';
    const OUTPUT_MODE_INLINE = 'inline';
    const OUTPUT_MODE_STRING = 'string';

    const OUTPUT_MODES = [
        self::OUTPUT_MODE_DOWNLOAD,
        self::OUTPUT_MODE_FILE,
        self::OUTPUT_MODE_INLINE,
        self::OUTPUT_MODE_STRING,
    ];

    const FONT_STYLE_REGUALAR = 'regular';
    const FONT_STYLE_ITALIC = 'italic';
    const FONT_STYLE_BOLD = 'bold';
    const FONT_STYLE_BOLDITALIC = 'bolditalic';

    const ORIENTATION_LANDSCAPE = 'landscape';
    const ORIENTATION_PORTRAIT = 'portrait';

    /**
     * @var string|null
     */
    protected $htmlContent;
    /**
     * @var string|null
     */
    protected $filename;
    /**
     * @var string|null
     */
    protected $outputMode;
    /**
     * @var string|null
     */
    protected $folder;
    /** @var string|null */
    protected $mediaType;
    /** @var array|null */
    protected $fonts;
    /** @var array|null */
    protected $margins;
    /** @var array|string|null */
    protected $format;
    /** @var string|null */
    protected $orientation;
    /** @var string|null */
    protected $templateFilePath;
    /** @var callable|null */
    protected $beforeCreateInstanceCallback;
    /** @var callable|null */
    protected $beforeOutputPdfCallback;

    /**
     * Return an unique type alias.
     */
    abstract public static function getType(): string;

    /**
     * @return mixed
     */
    public function getHtmlContent(): ?string
    {
        return $this->htmlContent;
    }

    /**
     * @param mixed $htmlContent
     */
    public function setHtmlContent($htmlContent): self
    {
        $this->htmlContent = $htmlContent;

        return $this;
    }

    abstract public function render(): void;

    /**
     * @return string
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return string
     */
    public function getOutputMode(): ?string
    {
        return $this->outputMode;
    }

    public function setOutputMode(string $outputMode): self
    {
        if (!\in_array($outputMode, $this->getSupportedOutputModes())) {
            trigger_error('Invalid output mode for '.static::class.'. Will fallback to default.');
        } else {
            $this->outputMode = $outputMode;
        }

        return $this;
    }

    abstract public function getSupportedOutputModes(): array;

    public function getFolder(): ?string
    {
        return $this->folder;
    }

    /**
     * Absolute folder where to store pdf.
     */
    public function setFolder(string $folder): self
    {
        $this->folder = $folder;

        return $this;
    }

    public function getMediaType(): ?string
    {
        return $this->mediaType;
    }

    /**
     * @param string|null $mediaType
     */
    public function setMediaType(string $mediaType): self
    {
        $this->mediaType = $mediaType;

        return $this;
    }

    public function getFonts(): ?array
    {
        return $this->fonts;
    }

    public function setFonts(array $fonts): self
    {
        $this->fonts = $fonts;

        return $this;
    }

    /**
     * @param string $filepath Absolute filepath to the font file
     * @param string $family   Font family name
     * @param string $style    Font style (regular, italic, ...), see AbstractPdfCreator::FONT_STYLE constants
     * @param string $weight   Font weight
     *
     * @return $this
     */
    public function addFont(string $filepath, string $family, string $style, string $weight): self
    {
        $this->fonts[] = [
            'filepath' => $filepath,
            'family' => $family,
            'style' => $style,
            'weight' => $weight,
        ];
    }

    public function getMargins(): ?array
    {
        return $this->margins;
    }

    /**
     * Set document margins.
     *
     * @param array|null $margins
     */
    public function setMargins(?int $top, ?int $right = null, ?int $bottom = null, ?int $left = null): self
    {
        $this->margins = [
            'top' => $top,
            'right' => $right,
            'bottom' => $bottom,
            'left' => $left,
        ];

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    /**
     * Set the document format.
     *
     * @param string|array $format A format type like A4, A5, Letter, Legal,... or an array of integers (width and height in mm).
     *
     * @return $this
     */
    public function setFormat($format): self
    {
        $this->format = $format;

        return $this;
    }

    public function getOrientation(): ?string
    {
        return $this->orientation;
    }

    /**
     * Set orientation. Use AbstractPdfCreator::ORIENTATION_LANDSCAPE or AbstractPdfCreator::ORIENTATION_PORTRAIT.
     */
    public function setOrientation(string $orientation): self
    {
        $this->orientation = $orientation;

        return $this;
    }

    public function getTemplateFilePath(): ?string
    {
        return $this->templateFilePath;
    }

    /**
     * Set the absolute path to a pdf template file.
     *
     * @param string|null $templateFilePath
     */
    public function setTemplateFilePath(string $templateFilePath): self
    {
        $this->templateFilePath = $templateFilePath;

        return $this;
    }

    public function getBeforeCreateInstanceCallback(): ?callable
    {
        return $this->beforeCreateInstanceCallback;
    }

    /**
     * Add an callback to modify constructor parameters for pdf library.
     * Callback gets an BeforeCreateLibraryInstanceCallback object as parameter and should return an BeforeCreateLibraryInstanceCallback object.
     */
    public function setBeforeCreateInstanceCallback(?callable $beforeCreateInstanceCallback): self
    {
        $this->beforeCreateInstanceCallback = $beforeCreateInstanceCallback;

        return $this;
    }

    public function getBeforeOutputPdfCallback(): ?callable
    {
        return $this->beforeOutputPdfCallback;
    }

    /**
     * Add an callback to modify the configuration or parameters before outputting the pdf file.
     * Callback gets an BeforeOutputPdfCallback object as parameter and should return an BeforeOutputPdfCallback object.
     */
    public function setBeforeOutputPdfCallback(?callable $beforeOutputPdfCallback): self
    {
        $this->beforeOutputPdfCallback = $beforeOutputPdfCallback;

        return $this;
    }
}

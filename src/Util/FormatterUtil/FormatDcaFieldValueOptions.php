<?php

namespace HeimrichHannot\UtilsBundle\Util\FormatterUtil;

/**
 * @codeCoverageIgnore This is a simple DTO.
 */
class FormatDcaFieldValueOptions
{
    public bool $preserveEmptyArrayValues = false;
    public bool $localize = true;
    public bool $loadDca = true;
    public bool $cacheOptions = true;
    public bool $replaceInsertTags = true;
    public ?array $dcaOverride = null;
    public string $arrayJoiner = ', ';
    /** @internal Used for caching options. */
    public ?array $optionsCache = null;

    public static function create(): FormatDcaFieldValueOptions
    {
        return new self();
    }

    public function setPreserveEmptyArrayValues(bool $preserveEmptyArrayValues): FormatDcaFieldValueOptions
    {
        $this->preserveEmptyArrayValues = $preserveEmptyArrayValues;
        return $this;
    }

    public function setLocalize(bool $localize): FormatDcaFieldValueOptions
    {
        $this->localize = $localize;
        return $this;
    }

    public function setLoadDca(bool $loadDca): FormatDcaFieldValueOptions
    {
        $this->loadDca = $loadDca;
        return $this;
    }

    public function setCacheOptions(bool $cacheOptions): FormatDcaFieldValueOptions
    {
        $this->cacheOptions = $cacheOptions;
        return $this;
    }

    public function setReplaceInsertTags(bool $replaceInsertTags): FormatDcaFieldValueOptions
    {
        $this->replaceInsertTags = $replaceInsertTags;
        return $this;
    }

    /**
     * Override the DCA field settings. If not set, the DCA field settings will be used.
     * @param array|null $dcaOverride
     * @return $this
     */
    public function setDcaOverride(?array $dcaOverride): FormatDcaFieldValueOptions
    {
        $this->dcaOverride = $dcaOverride;
        return $this;
    }

    /**
     * The string that joins array values. Default: ', '.
     * @param string $arrayJoiner
     * @return $this
     */
    public function setArrayJoiner(string $arrayJoiner): FormatDcaFieldValueOptions
    {
        $this->arrayJoiner = $arrayJoiner;
        return $this;
    }

}
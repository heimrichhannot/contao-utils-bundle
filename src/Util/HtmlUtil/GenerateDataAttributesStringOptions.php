<?php

namespace HeimrichHannot\UtilsBundle\Util\HtmlUtil;

class GenerateDataAttributesStringOptions
{
    private bool $xhtml = false;
    private bool                                      $normalizeKeys = true;
    private GenerateDataAttributesStringArrayHandling $arrayHandling = GenerateDataAttributesStringArrayHandling::REDUCE;

    public static function create(): self
    {
        return new self();
    }

    public function isXhtml(): bool
    {
        return $this->xhtml;
    }

    public function setXhtml(bool $xhtml): GenerateDataAttributesStringOptions
    {
        $this->xhtml = $xhtml;
        return $this;
    }

    public function isNormalizeKeys(): bool
    {
        return $this->normalizeKeys;
    }

    /**
     * Array keys are normalized to lowercase dash-cased strings (e.g. Foo Bar_player is transformed to foo-bar-player)
     */
    public function setNormalizeKeys(bool $normalizeKeys): GenerateDataAttributesStringOptions
    {
        $this->normalizeKeys = $normalizeKeys;
        return $this;
    }

    public function getArrayHandling(): GenerateDataAttributesStringArrayHandling
    {
        return $this->arrayHandling;
    }

    public function setArrayHandling(GenerateDataAttributesStringArrayHandling $arrayHandling): GenerateDataAttributesStringOptions
    {
        $this->arrayHandling = $arrayHandling;
        return $this;
    }
}
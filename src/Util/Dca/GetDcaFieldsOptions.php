<?php

namespace HeimrichHannot\UtilsBundle\Util\Dca;

class GetDcaFieldsOptions
{
    /**
     * 'onlyDatabaseFields' => false,
     * 'allowedInputTypes' => [],
     * 'evalConditions' => [],
     * 'localizeLabels' => false,
     * 'skipSorting' => false,
     */

    private bool $onlyDatabaseFields = false;
    private array $allowedInputTypes = [];
    private array $evalConditions = [];
    private bool $localizeLabels = false;
    private bool $skipSorting = false;

    public function isOnlyDatabaseFields(): bool
    {
        return $this->onlyDatabaseFields;
    }

    public static function create(): self
    {
        return new self();
    }

    /**
     * Return only fields with sql definition. Default false
     */
    public function setOnlyDatabaseFields(bool $onlyDatabaseFields): GetDcaFieldsOptions
    {
        $this->onlyDatabaseFields = $onlyDatabaseFields;
        return $this;
    }

    public function getAllowedInputTypes(): array
    {
        return $this->allowedInputTypes;
    }

    /**
     * Return only fields of given types.
     */
    public function setAllowedInputTypes(array $allowedInputTypes): GetDcaFieldsOptions
    {
        $this->allowedInputTypes = $allowedInputTypes;
        return $this;
    }

    public function isOnlyAllowedInputTypes(): bool
    {
        return !empty($this->allowedInputTypes);
    }

    public function getEvalConditions(): array
    {
        return $this->evalConditions;
    }

    /**
     * Return only fields with given eval key-value-pairs.
     */
    public function setEvalConditions(array $evalConditions): GetDcaFieldsOptions
    {
        $this->evalConditions = $evalConditions;
        return $this;
    }

    public function isHasEvalConditions(): bool
    {
        return !empty($this->evalConditions);
    }

    public function isLocalizeLabels(): bool
    {
        return $this->localizeLabels;
    }

    /**
     * Return also the field labels (key = field name, value = field label). Default false
     */
    public function setLocalizeLabels(bool $localizeLabels): GetDcaFieldsOptions
    {
        $this->localizeLabels = $localizeLabels;
        return $this;
    }

    public function isSkipSorting(): bool
    {
        return $this->skipSorting;
    }

    /**
     * Skip sorting fields by field name alphabetical. Default false
     */
    public function setSkipSorting(bool $skipSorting): GetDcaFieldsOptions
    {
        $this->skipSorting = $skipSorting;
        return $this;
    }


}
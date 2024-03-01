<?php

namespace HeimrichHannot\UtilsBundle\Dca;

class AuthorFieldOptions extends DcaFieldOptions
{
    /** @var string  */
    protected $type = AuthorField::TYPE_USER;
    /** @var string  */
    protected $fieldNamePrefix = '';
    /** @var bool  */
    protected $useDefaultLabel = true;
    /** @var bool  */
    protected $exclude = true;
    /** @var bool  */
    protected $search = true;
    /** @var bool  */
    protected $filter = true;

    public function setType(string $type): AuthorFieldOptions
    {
        $this->type = $type;
        return $this;
    }

    public function hasFieldNamePrefix(): bool
    {
        return !empty($this->fieldNamePrefix);
    }

    public function getFieldNamePrefix(): string
    {
        return $this->fieldNamePrefix;
    }

    public function setFieldNamePrefix(string $fieldNamePrefix): AuthorFieldOptions
    {
        $this->fieldNamePrefix = $fieldNamePrefix;
        return $this;
    }

    public function isUseDefaultLabel(): bool
    {
        return $this->useDefaultLabel;
    }

    public function setUseDefaultLabel(bool $useDefaultLabel): AuthorFieldOptions
    {
        $this->useDefaultLabel = $useDefaultLabel;
        return $this;
    }

    public function isExclude(): bool
    {
        return $this->exclude;
    }

    public function setExclude(bool $exclude): AuthorFieldOptions
    {
        $this->exclude = $exclude;
        return $this;
    }

    public function isSearch(): bool
    {
        return $this->search;
    }

    public function setSearch(bool $search): AuthorFieldOptions
    {
        $this->search = $search;
        return $this;
    }

    public function isFilter(): bool
    {
        return $this->filter;
    }

    public function setFilter(bool $filter): AuthorFieldOptions
    {
        $this->filter = $filter;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
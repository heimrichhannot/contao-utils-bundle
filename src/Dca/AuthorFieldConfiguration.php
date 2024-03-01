<?php

namespace HeimrichHannot\UtilsBundle\Dca;

class AuthorFieldConfiguration extends DcaFieldConfiguration
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

    public function setType(string $type): AuthorFieldConfiguration
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

    public function setFieldNamePrefix(string $fieldNamePrefix): AuthorFieldConfiguration
    {
        $this->fieldNamePrefix = $fieldNamePrefix;
        return $this;
    }

    public function isUseDefaultLabel(): bool
    {
        return $this->useDefaultLabel;
    }

    public function setUseDefaultLabel(bool $useDefaultLabel): AuthorFieldConfiguration
    {
        $this->useDefaultLabel = $useDefaultLabel;
        return $this;
    }

    public function isExclude(): bool
    {
        return $this->exclude;
    }

    public function setExclude(bool $exclude): AuthorFieldConfiguration
    {
        $this->exclude = $exclude;
        return $this;
    }

    public function isSearch(): bool
    {
        return $this->search;
    }

    public function setSearch(bool $search): AuthorFieldConfiguration
    {
        $this->search = $search;
        return $this;
    }

    public function isFilter(): bool
    {
        return $this->filter;
    }

    public function setFilter(bool $filter): AuthorFieldConfiguration
    {
        $this->filter = $filter;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
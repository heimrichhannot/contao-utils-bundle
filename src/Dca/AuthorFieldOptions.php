<?php

namespace HeimrichHannot\UtilsBundle\Dca;

class AuthorFieldOptions
{
    protected string $table;
    protected string $type = AuthorField::TYPE_USER;
    protected string $fieldNamePrefix = '';
    protected bool $useDefaultLabel = true;
    protected bool $exclude = true;
    protected bool $search = true;
    protected bool $filter = true;

    /**
     * @param string $table
     */
    public function __construct(string $table)
    {
        $this->table = $table;
    }


    public function getTable(): string
    {
        return $this->table;
    }

    public function getType(): string
    {
        return $this->type;
    }

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
}
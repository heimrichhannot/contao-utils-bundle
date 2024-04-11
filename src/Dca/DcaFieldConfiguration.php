<?php

namespace HeimrichHannot\UtilsBundle\Dca;

class DcaFieldConfiguration
{
    private ?int $flag = null;
    protected bool $exclude = false;
    protected bool $search = false;
    protected bool $filter = false;
    protected bool $sorting = false;

    /**
     * @param string $table
     */
    public function __construct(private string $table)
    {

    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function isSorting(): bool
    {
        return $this->sorting;
    }

    public function setSorting(bool $sorting): DcaFieldConfiguration
    {
        $this->sorting = $sorting;
        return $this;
    }

    public function getFlag(): ?int
    {
        return $this->flag;
    }

    public function setFlag(?int $flag): DcaFieldConfiguration
    {
        $this->flag = $flag;
        return $this;
    }

    public function isExclude(): bool
    {
        return $this->exclude;
    }

    public function setExclude(bool $exclude): DcaFieldConfiguration
    {
        $this->exclude = $exclude;
        return $this;
    }

    public function isSearch(): bool
    {
        return $this->search;
    }

    public function setSearch(bool $search): DcaFieldConfiguration
    {
        $this->search = $search;
        return $this;
    }

    public function isFilter(): bool
    {
        return $this->filter;
    }

    public function setFilter(bool $filter): DcaFieldConfiguration
    {
        $this->filter = $filter;
        return $this;
    }
}
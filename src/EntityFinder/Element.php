<?php

namespace HeimrichHannot\UtilsBundle\EntityFinder;

class Element
{
    private int $id;
    private string $table;
    private ?string $description;
    private ?\Closure $parents;

    /**
     * @param int $id
     * @param string $table
     * @param string|null $description
     * @param \Closure(string $table, int $id):\Iterator|null $parents A closure that returns an iterator of parent elements
     */
    public function __construct(int $id, string $table, string $description = null, \Closure $parents = null)
    {
        $this->id = $id;
        $this->table = $table;
        $this->description = $description;
        $this->parents = $parents;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return \Closure(string $table, int $id):\Iterator|null
     */
    public function getParents(): ?\Closure
    {
        return $this->parents;
    }
}
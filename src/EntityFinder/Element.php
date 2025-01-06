<?php

namespace HeimrichHannot\UtilsBundle\EntityFinder;

class Element
{
    private int $id;
    private string $table;
    private ?string $description;
    private ?iterable $parents;

    /**
     * @param int $id
     * @param string $table
     * @param string|null $description
     * @param \Traversable|null $parents A closure that returns an iterator of parent elements
     */
    public function __construct(int $id, string $table, ?string $description = null, ?iterable $parents = null)
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

    public function getParents(): ?iterable
    {
        return $this->parents;
    }
}
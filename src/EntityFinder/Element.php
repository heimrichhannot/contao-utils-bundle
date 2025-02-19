<?php

namespace HeimrichHannot\UtilsBundle\EntityFinder;

class Element
{

    /**
     * @param iterable|null $parents A closure that returns an iterator of parent elements
     */
    public function __construct(
        public readonly int     $id,
        public readonly string  $table,
        public readonly ?string $description = null,
        public readonly ?iterable $parents = null
    )
    {
    }

    /**
     * @deprecated
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @deprecated
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @deprecated
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getParents(): ?iterable
    {
        return $this->parents;
    }
}
<?php

namespace HeimrichHannot\UtilsBundle\Event;

use HeimrichHannot\UtilsBundle\EntityFinder\Element;
use Symfony\Contracts\EventDispatcher\Event;

class EntityFinderFindEvent extends Event
{
    private ?Element $element = null;

    public function __construct(
        public readonly string $table,
        public readonly int $id
    ) {
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
    public function getId(): int
    {
        return $this->id;
    }

    public function setElement(Element $element): void
    {
        $this->element = $element;
        $this->stopPropagation();
    }

    public function getElement(): ?Element
    {
        return $this->element;
    }



}
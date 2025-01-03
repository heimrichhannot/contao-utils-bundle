<?php

namespace HeimrichHannot\UtilsBundle\Event;

use HeimrichHannot\UtilsBundle\EntityFinder\Element;
use Symfony\Contracts\EventDispatcher\Event;

class EntityFinderFindEvent extends Event
{
    private string $table;
    private int $id;
    private ?Element $element;

    public function __construct(string $table, int $id)
    {
        $this->table = $table;
        $this->id = $id;
    }

    public function getTable(): string
    {
        return $this->table;
    }

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
<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Event;

use HeimrichHannot\UtilsBundle\EntityFinder\EntityFinderHelper;
use Symfony\Contracts\EventDispatcher\Event;

class ExtendEntityFinderEvent extends Event
{
    private ?string $output = null;

    public function __construct(
        private string $table,
        private int|string $id,
        private array $parents,
        private array $inserttags,
        private EntityFinderHelper $entityFinderHelper,
        private bool $onlyText = false
    )
    {
    }

    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    public function addParent(string $table, $id): void
    {
        $this->parents[] = ['table' => $table, 'id' => $id];
    }

    public function getParents(): array
    {
        return $this->parents;
    }

    public function setParents(array $parents): void
    {
        $this->parents = $parents;
    }

    public function getOutput(): ?string
    {
        return $this->output;
    }

    public function setOutput(?string $output): void
    {
        $this->output = $output;
    }

    public function isOnlyText(): bool
    {
        return $this->onlyText;
    }

    public function addInserttag(string $inserttag): void
    {
        $this->inserttags[] = $inserttag;
    }

    public function getInserttags(): array
    {
        return $this->inserttags;
    }

    public function setInserttags(array $inserttags): void
    {
        $this->inserttags = $inserttags;
    }

    public function getEntityFinderHelper(): EntityFinderHelper
    {
        return $this->entityFinderHelper;
    }
}

<?php

namespace HeimrichHannot\UtilsBundle\Dca;

class DcaFieldConfiguration
{
    public function __construct(private string $table)
    {
    }

    public function getTable(): string
    {
        return $this->table;
    }
}
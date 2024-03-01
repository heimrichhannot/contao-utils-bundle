<?php

namespace HeimrichHannot\UtilsBundle\Dca;

class DcaFieldOptions
{
    /**
     * @var string
     */
    private $table;

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
}
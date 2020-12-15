<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\PdfCreator;

class BeforeCreateLibraryInstanceCallback
{
    /**
     * @var array
     */
    protected $constructorParameters;

    /**
     * BeforeCreateLibraryInstanceCallback constructor.
     */
    public function __construct(array $constructorParameters = [])
    {
        $this->constructorParameters = $constructorParameters;
    }

    public function getConstructorParameters(): array
    {
        return $this->constructorParameters;
    }

    public function setConstructorParameters(array $constructorParameters): void
    {
        $this->constructorParameters = $constructorParameters;
    }
}

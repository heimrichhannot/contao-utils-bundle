<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\PdfCreator;

class BeforeOutputPdfCallback
{
    protected $libraryInstance;
    /**
     * @var array
     */
    protected $outputParameters;

    public function __construct($libraryInstance, array $outputParameters = [])
    {
        $this->libraryInstance = $libraryInstance;
        $this->outputParameters = $outputParameters;
    }

    /**
     * @return mixed
     */
    public function getLibraryInstance()
    {
        return $this->libraryInstance;
    }

    /**
     * @param mixed $libraryInstance
     */
    public function setLibraryInstance($libraryInstance): void
    {
        $this->libraryInstance = $libraryInstance;
    }

    public function getOutputParameters(): array
    {
        return $this->outputParameters;
    }

    public function setOutputParameters(array $outputParameters): void
    {
        $this->outputParameters = $outputParameters;
    }
}

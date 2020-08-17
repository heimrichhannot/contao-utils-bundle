<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\PdfCreator;

use HeimrichHannot\UtilsBundle\PdfCreator\Concrete\MpdfCreator;

class PdfCreatorFactory
{
    /**
     * Return supported pdf creator types.
     *
     * @return array
     */
    public static function getTypes()
    {
        return array_keys(static::getPdfCreatorRegistry());
    }

    /**
     * Return a pdf creator instance for given type or null, if no type is registered for given type.
     */
    public static function createInstance(string $type): ?AbstractPdfCreator
    {
        $types = static::getPdfCreatorRegistry();

        if (isset($types[$type])) {
            return new $types[$type]();
        }

        return null;
    }

    protected static function getPdfCreatorRegistry()
    {
        return [
            MpdfCreator::getType() => MpdfCreator::class,
        ];
    }
}

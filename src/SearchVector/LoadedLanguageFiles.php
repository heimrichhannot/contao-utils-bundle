<?php

namespace HeimrichHannot\UtilsBundle\SearchVector;

use Contao\System;

/**
 * Reads the names of the language files Contao has loaded so far, which Contao keeps in a protected property only.
 *
 * @internal
 */
abstract class LoadedLanguageFiles extends System
{
    /**
     * @return list<string> Empty if the Contao version does not track loaded language files
     */
    public static function getNames(): array
    {
        if (!\property_exists(System::class, 'arrLanguageFiles')) {
            return [];
        }

        return \array_map('strval', \array_keys(System::$arrLanguageFiles));
    }
}

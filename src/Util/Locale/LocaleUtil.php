<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util\Locale;

class LocaleUtil
{
    /**
     * Ensure language specific line breaks.
     *
     * Supported languages:
     * - czech: Replace line break after a one-character word with non-breaking space (html code (&nbsp;))
     */
    public function ensureLineBreaks(string $buffer, string $language = 'en'): string
    {
        switch ($language) {
            case 'cs':
                // in czech language, one-syllable words should not stand alone at the end  (use &nbsp; instead of whitespace)
                $buffer = preg_replace('/(\s\w{1})(\s)/', '$1&nbsp;', $buffer);

                break;
        }

        return $buffer;
    }
}

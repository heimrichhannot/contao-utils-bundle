<?php

namespace HeimrichHannot\UtilsBundle\SearchVector\Stemmer;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Reduces a single word to its stem.
 *
 * Implementations are registered automatically when autoconfigured and can be selected per table with
 * SearchVectorFieldConfiguration::setStemmer(<service id>). The service id is usually the class name.
 */
#[AutoconfigureTag(self::TAG)]
interface StemmerInterface
{
    public const TAG = 'huh_utils.search_vector.stemmer';

    /**
     * @param string $word   Already lowercased, trimmed and stripped of accents (ä → a, ß → ss, é → e)
     * @param string $locale The language of the word as locale ID or language tag, e.g. "de", "de_CH" or "de-CH".
     *                       Return the word unchanged for languages the stemmer does not support.
     */
    public function stem(string $word, string $locale): string;
}

<?php

namespace HeimrichHannot\UtilsBundle\SearchVector\Normalizer;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Unifies different spellings of the same word before stemming, e.g. "Muenchen" and "München".
 *
 * Implementations are registered automatically when autoconfigured and can be selected per table with
 * SearchVectorFieldConfiguration::setNormalizer(<service id>). The service id is usually the class name.
 */
#[AutoconfigureTag(self::TAG)]
interface NormalizerInterface
{
    public const TAG = 'huh_utils.search_vector.normalizer';

    /**
     * @param string $word   Lowercased and trimmed, accents are kept (they are stripped after stemming)
     * @param string $locale The language of the word as locale ID or language tag, e.g. "de", "de_CH" or "de-CH".
     *                       Return the word unchanged for languages the normalizer does not support.
     */
    public function normalize(string $word, string $locale): string;
}

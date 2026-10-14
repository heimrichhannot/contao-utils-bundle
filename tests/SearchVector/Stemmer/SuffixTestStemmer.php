<?php

namespace HeimrichHannot\UtilsBundle\Tests\SearchVector\Stemmer;

use HeimrichHannot\UtilsBundle\SearchVector\Stemmer\StemmerInterface;

/**
 * Deterministic stemmer for tests, so they do not depend on the rules of a real stemmer library.
 *
 * Strips one German ending (-ungen, -ung, -en, -er, -e) from words with at least five letters. Words of other
 * languages are returned unchanged.
 */
final class SuffixTestStemmer implements StemmerInterface
{
    public function stem(string $word, string $locale): string
    {
        if (!\str_starts_with(\strtolower($locale), 'de') || \mb_strlen($word) < 5) {
            return $word;
        }

        return (string) \preg_replace('/(ungen|ung|en|er|e)$/u', '', $word, 1);
    }
}

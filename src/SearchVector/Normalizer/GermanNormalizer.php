<?php

namespace HeimrichHannot\UtilsBundle\SearchVector\Normalizer;

use Contao\CoreBundle\Util\LocaleUtil;

/**
 * Restores German umlauts written as "ae", "oe" and "ue", so "Muenchen" finds "München".
 *
 * Follows the rules of Lucene's GermanNormalizationFilter: "ae" and "oe" always become "ä" and "ö", "ue" becomes "ü"
 * unless it follows a vowel or "q", which keeps "Feuer", "Bauer" and "Quelle" intact. Lucene replaces the umlauts by
 * plain vowels right away. Here they are kept for the stemmer and stripped by the tokenizer afterwards, which leads to
 * the same result.
 *
 * Words of other languages than German are returned unchanged.
 */
class GermanNormalizer implements NormalizerInterface
{
    /** Ordinary state */
    private const STATE_DEFAULT = 0;

    /** After a vowel or "q", a following "u" does not start an umlaut */
    private const STATE_VOWEL = 1;

    /** After an "a", "o" or "u" that a following "e" turns into an umlaut */
    private const STATE_UMLAUT = 2;

    private const UMLAUTS = ['a' => 'ä', 'o' => 'ö', 'u' => 'ü'];

    public function normalize(string $word, string $locale): string
    {
        if ('de' !== LocaleUtil::getPrimaryLanguage($locale)) {
            return $word;
        }

        $chars = [];
        $state = self::STATE_DEFAULT;

        foreach (\mb_str_split($word) as $char) {
            switch ($char) {
                case 'a':
                case 'o':
                    $state = self::STATE_UMLAUT;
                    break;

                case 'u':
                    $state = self::STATE_DEFAULT === $state ? self::STATE_UMLAUT : self::STATE_VOWEL;
                    break;

                case 'e':
                    if (self::STATE_UMLAUT === $state) {
                        $last = \array_key_last($chars);
                        $chars[$last] = self::UMLAUTS[$chars[$last]];
                        $state = self::STATE_VOWEL;

                        // The "e" is dropped
                        continue 2;
                    }

                    $state = self::STATE_VOWEL;
                    break;

                case 'i':
                case 'q':
                case 'y':
                case 'ä':
                case 'ö':
                case 'ü':
                    $state = self::STATE_VOWEL;
                    break;

                default:
                    $state = self::STATE_DEFAULT;
            }

            $chars[] = $char;
        }

        return \implode('', $chars);
    }
}

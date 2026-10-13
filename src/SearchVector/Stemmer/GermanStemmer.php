<?php

namespace HeimrichHannot\UtilsBundle\SearchVector\Stemmer;

use Contao\CoreBundle\Util\LocaleUtil;

/**
 * Very compact German stemmer.
 *
 * Heuristic, inspired by Snowball. Good for search, not linguistically perfect. Index and query are stemmed the
 * same way, so a wrong stem only matters if two unrelated words collapse into the same one.
 *
 * Words of other languages than German are returned unchanged.
 */
class GermanStemmer implements StemmerInterface
{
    public function stem(string $word, string $locale): string
    {
        $w = \mb_strtolower(\trim($word), 'UTF-8');

        if ($w === '' || 'de' !== LocaleUtil::getPrimaryLanguage($locale)) {
            return $w;
        }

        // strip leading and trailing punctuation
        $w = (string) \preg_replace('/^[^\p{L}\p{Nd}]+|[^\p{L}\p{Nd}]+$/u', '', $w);

        // plural fem. professions: "lehrerinnen" -> "lehrerin"
        $w = (string) \preg_replace('/innen$/u', 'in', $w);

        // protect words whose stem would otherwise be cut too far, the trailing "_" is removed below
        $w = (string) \preg_replace('/^m[üu]e?nchen$/u', 'munch_', $w);
        $w = (string) \preg_replace('/^m[üu]e?nster$/u', 'munst_', $w);
        $w = (string) \preg_replace('/kirche?n?$/u', 'kirch_', $w);
        $w = (string) \preg_replace('/ot$/u', 'ot_', $w);

        // diminutives: "mädchen", "fräulein"
        $w = (string) \preg_replace('/(chen|lein)$/u', '', $w);

        // derivational suffixes, only the first matching rule is applied
        $rules = [
            '/(igkeit)(en|er)?$/u' => 'ig',       // wichtigkeit(en) → wichtig
            '/(lichkeit)(en|er)?$/u' => 'lich',   // möglichkeiten → möglich
            '/(keit)(en|er)?$/u' => '',           // ewigkeit → ewig
            '/(heit)(en|er)?$/u' => '',           // freiheit → frei
            '/(ung)(en|s)?$/u' => '',             // lieferung(en) → liefer
            '/(isch|lich|ig|end|igst)$/u' => '',  // klassisch → klass
        ];

        foreach ($rules as $pattern => $replacement) {
            $after = \preg_replace($pattern, $replacement, $w);

            if ($after !== null && $after !== $w) {
                $w = $after;
                break;
            }
        }

        // superlatives: beste, besten, bestem, bester
        $w = (string) \preg_replace('/(sten|stem|ster|ste)$/u', '', $w);

        // common plural and verb endings
        return (string) \preg_replace('/_|(ern|er|en|em|es|e|n|s|t)$/u', '', $w);
    }
}

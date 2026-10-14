<?php

namespace HeimrichHannot\UtilsBundle\SearchVector;

use HeimrichHannot\UtilsBundle\SearchVector\Normalizer\NormalizerInterface;
use HeimrichHannot\UtilsBundle\SearchVector\Stemmer\StemmerInterface;

/**
 * Splits text into normalized and optionally stemmed words.
 *
 * Shared by the indexer and the query side, so stored vectors and search terms are always normalized the same way.
 */
class Tokenizer
{
    /**
     * Tokenize free text into normalized, optionally stemmed words, preserving their order.
     *
     * Words are lowercased, normalized, stemmed and then stripped of accents, as normalizers and stemmers rely on
     * accents (e.g. German umlauts).
     *
     * Example: `Tokenizer::tokenize('Häuser in München', 'de', new SnowballStemmer()) === ['haus', 'in', 'munch']`
     *
     * @param string $locale The language of the text, passed to the normalizer and the stemmer
     *
     * @return list<string>
     */
    public static function tokenize(string $text, string $locale, ?StemmerInterface $stemmer = null, ?NormalizerInterface $normalizer = null): array
    {
        // Compose decomposed characters ("u" + combining diaeresis → "ü"), so normalizers and stemmers get the same
        // characters for both spellings
        if (\class_exists(\Normalizer::class)) {
            $text = \Normalizer::normalize($text, \Normalizer::FORM_C) ?: $text;
        }

        // Split on anything that cannot be part of a word, but keep hyphens so compounds such as
        // "Baden-Württemberg" stay a single token. Combining marks without a composed form belong to their word.
        $words = \preg_split('/[^\p{L}\p{M}\p{Nd}-]+/u', $text, -1, \PREG_SPLIT_NO_EMPTY) ?: [];

        $tokens = [];

        foreach ($words as $word) {
            $token = \trim(\mb_strtolower($word, 'UTF-8'), '-');

            if ($token !== '' && $normalizer) {
                $token = $normalizer->normalize($token, $locale);
            }

            if ($token !== '' && $stemmer) {
                $token = $stemmer->stem($token, $locale);
            }

            $token = static::foldAccents($token);

            if ($token !== '') {
                $tokens[] = $token;
            }
        }

        return $tokens;
    }

    /**
     * Parses a user search query into phrases of tokens.
     *
     * Commas separate phrases (OR), the terms of a phrase are combined with AND. Words wrapped in double quotes stay a
     * single term, so their order matters. Quoted and unquoted words can be mixed, an unclosed quote is ignored.
     *
     * Example: `Tokenizer::parseQuery('Altstadt Führungen, Museum "am Abend"', 'de', new SnowballStemmer())`
     * returns `[['altstadt', 'fuhrung'], ['museum', 'am abend']]`.
     *
     * @param string $locale The language of the query, passed to the normalizer and the stemmer
     *
     * @return list<list<string>>
     */
    public static function parseQuery(string $query, string $locale, ?StemmerInterface $stemmer = null, ?NormalizerInterface $normalizer = null): array
    {
        $phrases = [];

        foreach (self::splitPhrases($query) as $phrase) {
            $terms = [];

            // Either a quoted segment (group 1) or a run of unquoted words
            \preg_match_all('/"([^"]*)"|[^"]+/', $phrase, $segments, \PREG_SET_ORDER);

            foreach ($segments as $segment) {
                if (!\array_key_exists(1, $segment)) {
                    \array_push($terms, ...static::tokenize($segment[0], $locale, $stemmer, $normalizer));
                } elseif ($tokens = static::tokenize($segment[1], $locale, $stemmer, $normalizer)) {
                    $terms[] = \implode(' ', $tokens);
                }
            }

            if ($terms) {
                $phrases[] = \array_values(\array_unique($terms));
            }
        }

        return $phrases;
    }

    /**
     * Lowercases a word and strips accents: ä → a, é → e, ñ → n, å → a. Letters without a decomposition into base letter
     * and accent are transliterated: ß → ss, æ → ae, ø → o, œ → oe, ð → d, þ → th, ł → l, đ → d, ı → i.
     */
    public static function foldAccents(string $word): string
    {
        $word = \strtr(\mb_strtolower(\trim($word), 'UTF-8'), [
            'ä' => 'a', 'ö' => 'o', 'ü' => 'u', 'ß' => 'ss',
            'æ' => 'ae', 'ø' => 'o', 'œ' => 'oe', 'ð' => 'd', 'þ' => 'th', 'ł' => 'l', 'đ' => 'd', 'ı' => 'i',
        ]);

        if (\class_exists(\Normalizer::class)) {
            $decomposed = \Normalizer::normalize($word, \Normalizer::FORM_D);

            if (\is_string($decomposed)) {
                $word = (string) \Normalizer::normalize((string) \preg_replace('/\p{Mn}+/u', '', $decomposed), \Normalizer::FORM_C);
            }
        }

        return $word;
    }

    /**
     * Splits by commas outside of double quotes. Quotes are kept to mark phrases. A quote inside quotes can be escaped
     * as "" or \" and is replaced by a space, as quotes are not searchable anyway.
     *
     * @return list<string>
     */
    private static function splitPhrases(string $input): array
    {
        $parts = [];
        $buffer = '';
        $inQuotes = false;
        $length = \strlen($input);

        for ($i = 0; $i < $length; ++$i) {
            $char = $input[$i];

            if ($inQuotes && ($char === '"' || $char === '\\') && ($input[$i + 1] ?? null) === '"') {
                $buffer .= ' ';
                ++$i;
                continue;
            }

            if ($char === '"') {
                $inQuotes = !$inQuotes;
                $buffer .= $char;
                continue;
            }

            if ($char === ',' && !$inQuotes) {
                $parts[] = $buffer;
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        $parts[] = $buffer;

        return \array_values(\array_filter(\array_map(trim(...), $parts), static fn (string $part): bool => $part !== ''));
    }
}

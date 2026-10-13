<?php

namespace HeimrichHannot\UtilsBundle\SearchVector;

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
     * Words are lowercased, stemmed and then stripped of accents, as stemmers rely on accents (e.g. German umlauts).
     *
     * Example: `Tokenizer::tokenize('Häuser in München', 'de', new SnowballStemmer()) === ['haus', 'in', 'munch']`
     *
     * @param string $locale The language of the text, passed to the stemmer
     *
     * @return list<string>
     */
    public static function tokenize(string $text, string $locale, ?StemmerInterface $stemmer = null): array
    {
        // Split on anything that cannot be part of a word, but keep hyphens so compounds such as
        // "Niederbayern-Oberpfalz" stay a single token.
        $words = \preg_split('/[^\p{L}\p{Nd}-]+/u', $text, -1, \PREG_SPLIT_NO_EMPTY) ?: [];

        $tokens = [];

        foreach ($words as $word) {
            $token = \trim(\mb_strtolower($word, 'UTF-8'), '-');

            if ($token !== '' && $stemmer) {
                $token = $stemmer->stem($token, $locale);
            }

            $token = static::normalize($token);

            if ($token !== '') {
                $tokens[] = $token;
            }
        }

        return $tokens;
    }

    /**
     * Parses a user search query into phrases of tokens.
     *
     * Commas separate phrases (OR), the words of a phrase are combined with AND. A phrase wrapped in double quotes
     * stays a single term, so its word order matters.
     *
     * Example: `Tokenizer::parseQuery('weihnachten bahn, "bei Nacht"', 'de', new SnowballStemmer())`
     * returns `[['weihnacht', 'bahn'], ['bei nacht']]`.
     *
     * @param string $locale The language of the query, passed to the stemmer
     *
     * @return list<list<string>>
     */
    public static function parseQuery(string $query, string $locale, ?StemmerInterface $stemmer = null): array
    {
        $phrases = [];

        foreach (self::splitPhrases($query) as $phrase) {
            if (\strlen($phrase) >= 2 && \str_starts_with($phrase, '"') && \str_ends_with($phrase, '"')) {
                $tokens = static::tokenize(\substr($phrase, 1, -1), $locale, $stemmer);
                $terms = $tokens ? [\implode(' ', $tokens)] : [];
            } else {
                $terms = \array_values(\array_unique(static::tokenize($phrase, $locale, $stemmer)));
            }

            if ($terms) {
                $phrases[] = $terms;
            }
        }

        return $phrases;
    }

    /**
     * Lowercases a word and strips accents: ä → a, ß → ss, é → e.
     */
    public static function normalize(string $word): string
    {
        $word = \strtr(\mb_strtolower(\trim($word), 'UTF-8'), ['ä' => 'a', 'ö' => 'o', 'ü' => 'u', 'ß' => 'ss']);

        if (\class_exists(\Normalizer::class)) {
            $decomposed = \Normalizer::normalize($word, \Normalizer::FORM_D);

            if (\is_string($decomposed)) {
                $word = (string) \Normalizer::normalize((string) \preg_replace('/\p{Mn}+/u', '', $decomposed), \Normalizer::FORM_C);
            }
        }

        return $word;
    }

    /**
     * Splits by commas outside of double quotes. Quotes are kept, a quote inside quotes can be escaped as "" or \".
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
                $buffer .= '"';
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

        return \array_values(\array_filter(\array_map('trim', $parts), static fn (string $part): bool => $part !== ''));
    }
}

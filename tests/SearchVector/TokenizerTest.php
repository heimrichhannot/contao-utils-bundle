<?php

namespace HeimrichHannot\UtilsBundle\Tests\SearchVector;

use HeimrichHannot\UtilsBundle\SearchVector\Normalizer\GermanNormalizer;
use HeimrichHannot\UtilsBundle\SearchVector\Normalizer\NormalizerInterface;
use HeimrichHannot\UtilsBundle\SearchVector\Stemmer\SnowballStemmer;
use HeimrichHannot\UtilsBundle\SearchVector\Stemmer\StemmerInterface;
use HeimrichHannot\UtilsBundle\SearchVector\Tokenizer;
use PHPUnit\Framework\TestCase;

class TokenizerTest extends TestCase
{
    /**
     * @dataProvider tokenizeProvider
     */
    public function testTokenizeWithoutStemmer(string $text, array $expected): void
    {
        $this->assertSame($expected, Tokenizer::tokenize($text, 'de'));
    }

    public static function tokenizeProvider(): \Generator
    {
        yield 'lowercase and order' => ['Freie Führungen', ['freie', 'fuhrungen']];
        yield 'hyphen compound kept' => ['Baden-Württemberg', ['baden-wurttemberg']];
        yield 'umlauts and sharp s' => ['Über Äpfel Öl Maß Straße', ['uber', 'apfel', 'ol', 'mass', 'strasse']];
        yield 'uppercase umlauts' => ['ÄÖÜ', ['aou']];
        yield 'accents' => ['Café', ['cafe']];
        yield 'punctuation' => ['Hallo, Welt! (Test); "Zitat" a/b', ['hallo', 'welt', 'test', 'zitat', 'a', 'b']];
        yield 'digits' => ['Zug 4711 um 10:30', ['zug', '4711', 'um', '10', '30']];
        yield 'percent and underscore are separators' => ['100% a_b', ['100', 'a', 'b']];
        yield 'leading and trailing hyphens' => ['-foo- --bar-- -', ['foo', 'bar']];
        yield 'hyphen only' => ['- -- ---', []];
        yield 'empty' => ['', []];
        yield 'only punctuation and whitespace' => [" ,;.\n\t", []];
        yield 'duplicates are kept' => ['museum museum', ['museum', 'museum']];
    }

    public function testTokenizeWithStemmer(): void
    {
        $stemmer = new SnowballStemmer();

        $this->assertSame(['freie', 'fuhrung'], Tokenizer::tokenize('Freie Führungen', 'de', $stemmer));
        $this->assertSame(['munch'], Tokenizer::tokenize('München', 'de', $stemmer));
        $this->assertSame(['haus', 'in', 'munch'], Tokenizer::tokenize('Häuser in München', 'de', $stemmer));
        $this->assertSame(['fuhrung', 'museum'], Tokenizer::tokenize('Führungen Museum', 'de', $stemmer));
    }

    public function testTokenizeStemsHyphenCompoundAsWhole(): void
    {
        $this->assertSame(['baden-wurttemberg'], Tokenizer::tokenize('Baden-Württemberg', 'de', new SnowballStemmer()));
    }

    public function testTokenizePassesLowercasedWordWithAccentsToStemmer(): void
    {
        $stemmer = new class implements StemmerInterface {
            public array $received = [];

            public function stem(string $word, string $locale): string
            {
                $this->received[] = $word;

                return $word;
            }
        };

        $tokens = Tokenizer::tokenize('Größe -Ärger- Über', 'de', $stemmer);

        $this->assertSame(['grosse', 'arger', 'uber'], $tokens);

        // Stemmers rely on umlauts, so accents are only folded after stemming
        $this->assertSame(['größe', 'ärger', 'über'], $stemmer->received);
    }

    public function testTokenizeFoldsAccentsOfStem(): void
    {
        $stemmer = new class implements StemmerInterface {
            public function stem(string $word, string $locale): string
            {
                return 'häuser' === $word ? 'haüs' : $word;
            }
        };

        $this->assertSame(['haus'], Tokenizer::tokenize('Häuser', 'de', $stemmer));
    }

    public function testNormalizerRunsBeforeStemmer(): void
    {
        $calls = [];
        $normalizer = new class($calls) implements NormalizerInterface {
            public function __construct(private array &$calls) {}

            public function normalize(string $word, string $locale): string
            {
                $this->calls[] = ['normalize', $word, $locale];

                return $word . '-n';
            }
        };
        $stemmer = new class($calls) implements StemmerInterface {
            public function __construct(private array &$calls) {}

            public function stem(string $word, string $locale): string
            {
                $this->calls[] = ['stem', $word, $locale];

                return $word . '-s';
            }
        };

        $tokens = Tokenizer::tokenize('Häuser Bäume', 'de_CH', $stemmer, $normalizer);

        $this->assertSame([
            ['normalize', 'häuser', 'de_CH'],
            ['stem', 'häuser-n', 'de_CH'],
            ['normalize', 'bäume', 'de_CH'],
            ['stem', 'bäume-n', 'de_CH'],
        ], $calls);
        $this->assertSame(['hauser-n-s', 'baume-n-s'], $tokens);
    }

    public function testNormalizerWithoutStemmer(): void
    {
        $this->assertSame(
            ['munchen', 'munchen', 'munchen'],
            Tokenizer::tokenize('Muenchen München Munchen', 'de', null, new GermanNormalizer())
        );
    }

    public function testNormalizerIsSkippedForEmptyTokens(): void
    {
        $normalizer = $this->createMock(NormalizerInterface::class);
        $normalizer->expects($this->never())->method('normalize');

        $this->assertSame([], Tokenizer::tokenize(' - , -- ', 'de', null, $normalizer));
    }

    public function testNormalizerAndSnowballStemmer(): void
    {
        $stemmer = new SnowballStemmer();
        $normalizer = new GermanNormalizer();

        $this->assertSame(['munch', 'munch', 'munch'], Tokenizer::tokenize('Muenchen München Munchen', 'de', $stemmer, $normalizer));
        $this->assertSame(['haus', 'haus', 'haus'], Tokenizer::tokenize('Häuser Haeuser Hauser', 'de', $stemmer, $normalizer));
        $this->assertSame(['feu', 'bau', 'quell'], Tokenizer::tokenize('Feuer Bauer Quelle', 'de', $stemmer, $normalizer));
    }

    public function testNormalizerIsLanguageSpecific(): void
    {
        $this->assertSame(['muenchen'], Tokenizer::tokenize('Muenchen', 'en', null, new GermanNormalizer()));
    }

    public function testParseQueryPassesNormalizer(): void
    {
        $normalizer = new GermanNormalizer();

        $this->assertSame([['munchen'], ['kolner dom']], Tokenizer::parseQuery('Muenchen, "Koelner Dom"', 'de', null, $normalizer));
        $this->assertSame(
            [['munch'], ['koln dom']],
            Tokenizer::parseQuery('Muenchen, "Koelner Dom"', 'de', new SnowballStemmer(), $normalizer)
        );
    }

    public function testParseQueryNormalizesQuotedPhrasesWordByWord(): void
    {
        $normalizer = new class implements NormalizerInterface {
            public array $received = [];

            public function normalize(string $word, string $locale): string
            {
                $this->received[] = [$word, $locale];

                return $word;
            }
        };

        Tokenizer::parseQuery('"Größe Fähre", Über', 'en', null, $normalizer);

        $this->assertSame([['größe', 'en'], ['fähre', 'en'], ['über', 'en']], $normalizer->received);
    }

    public function testTokenizePassesLocaleToStemmer(): void
    {
        $stemmer = $this->createLocaleRecordingStemmer();

        Tokenizer::tokenize('Hello World', 'en_GB', $stemmer);

        $this->assertSame(['en_GB', 'en_GB'], $stemmer->locales);
    }

    public function testParseQueryPassesLocaleToStemmer(): void
    {
        $stemmer = $this->createLocaleRecordingStemmer();

        Tokenizer::parseQuery('foo bar, "baz qux"', 'de_CH', $stemmer);

        $this->assertSame(['de_CH', 'de_CH', 'de_CH', 'de_CH'], $stemmer->locales);
    }

    public function testLocaleSelectsStemming(): void
    {
        $stemmer = new SnowballStemmer();

        $this->assertSame(['schul'], Tokenizer::tokenize('Schulen', 'de', $stemmer));
        $this->assertSame(['schulen'], Tokenizer::tokenize('Schulen', 'en', $stemmer));
        $this->assertSame([['schulen', 'museum']], Tokenizer::parseQuery('Schulen Museum', 'en', $stemmer));
    }

    public function testTokenizeDropsWordsThatStemToEmptyString(): void
    {
        $stemmer = new class implements StemmerInterface {
            public function stem(string $word, string $locale): string
            {
                return 'drop' === $word ? '' : $word;
            }
        };

        $this->assertSame(['keep', 'also'], Tokenizer::tokenize('keep drop also', 'de', $stemmer));
    }

    public function testDecomposedCharactersAreComposedBeforeSplitting(): void
    {
        // "u" followed by a combining diaeresis instead of the composed "ü"
        $decomposed = "Mu\u{0308}nchen Ha\u{0308}user";
        $composed = 'München Häuser';

        $this->assertSame(['munchen', 'hauser'], Tokenizer::tokenize($decomposed, 'de'));
        $this->assertSame(Tokenizer::tokenize($composed, 'de'), Tokenizer::tokenize($decomposed, 'de'));
        $this->assertSame(
            Tokenizer::tokenize($composed, 'de', new SnowballStemmer(), new GermanNormalizer()),
            Tokenizer::tokenize($decomposed, 'de', new SnowballStemmer(), new GermanNormalizer())
        );
        $this->assertSame([['munchen']], Tokenizer::parseQuery("Mu\u{0308}nchen", 'de'));
    }

    public function testCombiningMarksWithoutComposedFormStayInTheWord(): void
    {
        // "q" with a combining dot below has no composed form
        $this->assertSame(['aqb'], Tokenizer::tokenize("aq\u{0323}b", 'de'));
    }

    public function testFoldAccents(): void
    {
        $this->assertSame('munchen', Tokenizer::foldAccents(' München '));
        $this->assertSame('strasse', Tokenizer::foldAccents('STRAßE'));
        $this->assertSame('cafe', Tokenizer::foldAccents('Café'));
        $this->assertSame('', Tokenizer::foldAccents(' '));
    }

    /**
     * @dataProvider europeanLettersProvider
     */
    public function testFoldAccentsOfEuropeanLetters(string $word, string $expected): void
    {
        $this->assertSame($expected, Tokenizer::foldAccents($word));
    }

    public static function europeanLettersProvider(): \Generator
    {
        // Decomposable into base letter and accent
        yield 'French' => ['crème brûlée', 'creme brulee'];
        yield 'Spanish' => ['Ñandú', 'nandu'];
        yield 'Portuguese' => ['São Tomé', 'sao tome'];
        yield 'Nordic ring' => ['Ålesund', 'alesund'];
        yield 'Czech' => ['Čeština', 'cestina'];

        // Without decomposition, transliterated
        yield 'Danish and Norwegian' => ['Ærøskøbing', 'aeroskobing'];
        yield 'French ligature' => ['Œuvre', 'oeuvre'];
        yield 'Icelandic' => ['Þórður', 'thordur'];
        yield 'Polish' => ['Łódź', 'lodz'];
        yield 'Croatian' => ['Đurđevac', 'durdevac'];
        yield 'Turkish dotless i' => ['ılık', 'ilik'];
    }

    /**
     * @dataProvider parseQueryProvider
     */
    public function testParseQuery(string $query, array $expected, bool $stemmed = false): void
    {
        $this->assertSame($expected, Tokenizer::parseQuery($query, 'de', $stemmed ? new SnowballStemmer() : null));
    }

    public static function parseQueryProvider(): \Generator
    {
        yield 'single word' => ['Museum', [['museum']]];
        yield 'words are one phrase (AND)' => ['führungen museum', [['fuhrungen', 'museum']]];
        yield 'comma separates phrases (OR)' => ['kind, köln', [['kind'], ['koln']]];
        yield 'quoted phrase is one term' => ['"bei Nacht"', [['bei nacht']]];
        yield 'quoted phrase is normalized' => ['"Bei  Über-Nacht!"', [['bei uber-nacht']]];
        yield 'comma inside quotes does not split' => ['"a, b", c', [['a b'], ['c']]];
        yield 'quoted phrase with comma only' => ['"a, b"', [['a b']]];
        yield 'duplicate words removed' => ['foo Foo bar foo', [['foo', 'bar']]];
        yield 'duplicate phrases are kept' => ['foo, foo', [['foo'], ['foo']]];
        yield 'empty phrases dropped' => ['foo, , ,, bar,', [['foo'], ['bar']]];
        yield 'empty quotes dropped' => ['"", foo', [['foo']]];
        yield 'quotes with only punctuation dropped' => ['"..." foo', [['foo']]];
        yield 'empty query' => ['', []];
        yield 'only commas and whitespace' => [' , ,, ', []];
        yield 'doubled quote escape' => ['"say ""hi"" there", x', [['say hi there'], ['x']]];
        yield 'backslash quote escape' => ['"say \"hi\" there", x', [['say hi there'], ['x']]];
        yield 'escaped quote does not close, comma stays inside' => ['"say \"hi, there\" now", other', [['say hi there now'], ['other']]];
        yield 'unclosed quote is plain words' => ['"unclosed, phrase', [['unclosed', 'phrase']]];
        yield 'unclosed quote after words is plain words' => ['köln "bei Nacht', [['koln', 'bei', 'nacht']]];
        yield 'word and quoted phrase (AND)' => ['köln "bei Nacht"', [['koln', 'bei nacht']]];
        yield 'quoted phrase and word (AND)' => ['"bei Nacht" köln', [['bei nacht', 'koln']]];
        yield 'two quoted phrases (AND)' => ['"bei Nacht" "im Winter"', [['bei nacht', 'im winter']]];
        yield 'quoted phrase and words in second alternative' => ['kind, köln "bei Nacht" winter', [['kind'], ['koln', 'bei nacht', 'winter']]];
        yield 'comma inside quotes mixed with word' => ['"a, b" c', [['a b', 'c']]];
        yield 'quoted single word equals word' => ['haus "haus"', [['haus']]];
        yield 'quoted phrase stemmed' => ['"bei Nacht"', [['bei nacht']], true];
        yield 'stemmed words' => ['führungen museum, kind', [['fuhrung', 'museum'], ['kind']], true];
        yield 'umlauts stemmed and folded' => ['Häuser, München', [['haus'], ['munch']], true];
        yield 'duplicates after stemming removed' => ['schule schulen', [['schul']], true];
    }

    public function testParseQueryDocumentationExample(): void
    {
        $this->assertSame(
            [['fuhrung', 'museum'], ['kind'], ['koln']],
            Tokenizer::parseQuery('führungen museum, kind, köln', 'de', new SnowballStemmer())
        );
        $this->assertSame(
            [['fuhrung', 'museum'], ['koln', 'bei nacht']],
            Tokenizer::parseQuery('führungen museum, köln "bei Nacht"', 'de', new SnowballStemmer())
        );
    }

    /**
     * @dataProvider roundTripProvider
     */
    public function testQueryTermIsFoundInIndexedTokens(string $indexed, string $query): void
    {
        $stemmer = new SnowballStemmer();
        $vector = '| ' . \implode(' ', Tokenizer::tokenize($indexed, 'de', $stemmer)) . ' |';

        $phrases = Tokenizer::parseQuery($query, 'de', $stemmer);

        $this->assertNotEmpty($phrases);

        foreach ($phrases as $terms) {
            foreach ($terms as $term) {
                $this->assertStringContainsString($term, $vector, \sprintf('Query "%s" not found in vector %s', $query, $vector));
            }
        }
    }

    public static function roundTripProvider(): \Generator
    {
        yield 'different inflection and umlaut' => ['Lieferungen nach München', 'Lieferung münchen'];
        yield 'umlaut reversed' => ['Lieferungen nach Munchen', 'München'];
        yield 'plural vs singular' => ['Schulen und Kirchen', 'Schule Kirche'];
        yield 'compound part' => ['Baden-Württemberg', 'baden-württemberg'];
        yield 'quoted phrase' => ['Fahrt bei Nacht', '"bei Nacht"'];
        yield 'case' => ['STADTFÜHRUNGEN', 'stadtführungen'];
    }

    public function testQuotedPhraseDoesNotMatchWrongWordOrder(): void
    {
        $stemmer = new SnowballStemmer();
        $vector = '| ' . \implode(' ', Tokenizer::tokenize('Fahrt bei Nacht', 'de', $stemmer)) . ' |';

        $this->assertStringNotContainsString(Tokenizer::parseQuery('"Nacht bei"', 'de', $stemmer)[0][0], $vector);
    }

    private function createLocaleRecordingStemmer(): StemmerInterface
    {
        return new class implements StemmerInterface {
            public array $locales = [];

            public function stem(string $word, string $locale): string
            {
                $this->locales[] = $locale;

                return $word;
            }
        };
    }
}

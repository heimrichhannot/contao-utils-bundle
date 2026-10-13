<?php

namespace HeimrichHannot\UtilsBundle\Tests\SearchVector;

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
        yield 'lowercase and order' => ['Freie Strecke', ['freie', 'strecke']];
        yield 'hyphen compound kept' => ['Niederbayern-Oberpfalz', ['niederbayern-oberpfalz']];
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
        yield 'duplicates are kept' => ['bahn bahn', ['bahn', 'bahn']];
    }

    public function testTokenizeWithStemmer(): void
    {
        $stemmer = new SnowballStemmer();

        $this->assertSame(['freie', 'streck'], Tokenizer::tokenize('Freie Strecke', 'de', $stemmer));
        $this->assertSame(['munch'], Tokenizer::tokenize('München', 'de', $stemmer));
        $this->assertSame(['haus', 'in', 'munch'], Tokenizer::tokenize('Häuser in München', 'de', $stemmer));
        $this->assertSame(['weihnacht', 'bahn'], Tokenizer::tokenize('Weihnachten Bahn', 'de', $stemmer));
    }

    public function testTokenizeStemsHyphenCompoundAsWhole(): void
    {
        $this->assertSame(['niederbayern-oberpfalz'], Tokenizer::tokenize('Niederbayern-Oberpfalz', 'de', new SnowballStemmer()));
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
        $this->assertSame([['schulen', 'bahn']], Tokenizer::parseQuery('Schulen Bahn', 'en', $stemmer));
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

    public function testNormalize(): void
    {
        $this->assertSame('munchen', Tokenizer::normalize(' München '));
        $this->assertSame('strasse', Tokenizer::normalize('STRAßE'));
        $this->assertSame('cafe', Tokenizer::normalize('Café'));
        $this->assertSame('', Tokenizer::normalize(' '));
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
        yield 'single word' => ['Bahn', [['bahn']]];
        yield 'words are one phrase (AND)' => ['weihnachten bahn', [['weihnachten', 'bahn']]];
        yield 'comma separates phrases (OR)' => ['kind, dresden', [['kind'], ['dresden']]];
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
        yield 'unclosed quote after words is plain words' => ['dresden "bei Nacht', [['dresden', 'bei', 'nacht']]];
        yield 'word and quoted phrase (AND)' => ['dresden "bei Nacht"', [['dresden', 'bei nacht']]];
        yield 'quoted phrase and word (AND)' => ['"bei Nacht" dresden', [['bei nacht', 'dresden']]];
        yield 'two quoted phrases (AND)' => ['"bei Nacht" "im Winter"', [['bei nacht', 'im winter']]];
        yield 'quoted phrase and words in second alternative' => ['kind, dresden "bei Nacht" winter', [['kind'], ['dresden', 'bei nacht', 'winter']]];
        yield 'comma inside quotes mixed with word' => ['"a, b" c', [['a b', 'c']]];
        yield 'quoted single word equals word' => ['haus "haus"', [['haus']]];
        yield 'quoted phrase stemmed' => ['"bei Nacht"', [['bei nacht']], true];
        yield 'stemmed words' => ['weihnachten bahn, kind', [['weihnacht', 'bahn'], ['kind']], true];
        yield 'umlauts stemmed and folded' => ['Häuser, München', [['haus'], ['munch']], true];
        yield 'duplicates after stemming removed' => ['schule schulen', [['schul']], true];
    }

    public function testParseQueryDocumentationExample(): void
    {
        $this->assertSame(
            [['weihnacht', 'bahn'], ['kind'], ['dresd']],
            Tokenizer::parseQuery('weihnachten bahn, kind, dresden', 'de', new SnowballStemmer())
        );
        $this->assertSame(
            [['weihnacht', 'bahn'], ['dresd', 'bei nacht']],
            Tokenizer::parseQuery('weihnachten bahn, dresden "bei Nacht"', 'de', new SnowballStemmer())
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
        yield 'compound part' => ['Niederbayern-Oberpfalz', 'niederbayern-oberpfalz'];
        yield 'quoted phrase' => ['Fahrt bei Nacht', '"bei Nacht"'];
        yield 'case' => ['WEIHNACHTEN', 'weihnachten'];
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

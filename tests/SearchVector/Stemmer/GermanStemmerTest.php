<?php

namespace HeimrichHannot\UtilsBundle\Tests\SearchVector\Stemmer;

use HeimrichHannot\UtilsBundle\SearchVector\Stemmer\GermanStemmer;
use HeimrichHannot\UtilsBundle\SearchVector\Stemmer\StemmerInterface;
use HeimrichHannot\UtilsBundle\SearchVector\Tokenizer;
use PHPUnit\Framework\TestCase;

class GermanStemmerTest extends TestCase
{
    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(StemmerInterface::class, new GermanStemmer());
    }

    /**
     * @dataProvider stemProvider
     */
    public function testStem(string $word, string $expected): void
    {
        $this->assertSame($expected, (new GermanStemmer())->stem($word, 'de'));
    }

    public static function stemProvider(): \Generator
    {
        // plain endings
        yield 'plural -en' => ['weihnachten', 'weihnacht'];
        yield 'plural -n' => ['schulen', 'schul'];
        yield 'singular -e' => ['schule', 'schul'];
        yield 'final -e' => ['strecke', 'streck'];
        yield 'final -e after vowel' => ['freie', 'frei'];
        yield 'plural -s' => ['autos', 'auto'];
        yield 'short word without ending' => ['bahn', 'bah'];
        yield 'unchanged word' => ['wald', 'wald'];
        yield 'preposition' => ['bei', 'bei'];

        // derivational suffixes
        yield '-heit' => ['freiheit', 'frei'];
        yield '-keit' => ['ewigkeit', 'ewig'];
        yield '-igkeit(en)' => ['wichtigkeiten', 'wichtig'];
        yield '-lichkeit(en)' => ['moglichkeiten', 'moglich'];

        // protected words
        yield 'München, normalized' => ['munchen', 'munch'];
        yield 'München, umlaut' => ['münchen', 'munch'];
        yield 'Münster, normalized' => ['munster', 'munst'];
        yield 'Kirchen' => ['Kirchen', 'kirch'];
        yield 'Kirche' => ['kirche', 'kirch'];

        // diminutives
        yield 'diminutive -lein' => ['fraulein', 'frau'];

        // input handling
        yield 'uppercase' => ['STRECKE', 'streck'];
        yield 'surrounding whitespace' => ['  strecke ', 'streck'];
        yield 'surrounding punctuation' => ['"strecke".', 'streck'];
        yield 'empty' => ['', ''];
    }

    /**
     * @dataProvider germanLocaleProvider
     */
    public function testStemsGermanLocales(string $locale): void
    {
        $this->assertSame('weihnacht', (new GermanStemmer())->stem('Weihnachten', $locale));
    }

    public static function germanLocaleProvider(): \Generator
    {
        yield ['de'];
        yield ['de_DE'];
        yield ['de_CH'];
        yield ['de-CH'];
    }

    /**
     * @dataProvider otherLocaleProvider
     */
    public function testOtherLanguagesAreReturnedUnchanged(string $locale): void
    {
        $stemmer = new GermanStemmer();

        $this->assertSame('weihnachten', $stemmer->stem('weihnachten', $locale));
        $this->assertSame('weihnachten', $stemmer->stem('Weihnachten', $locale));
        $this->assertSame('schulen', $stemmer->stem(' Schulen ', $locale));
        $this->assertSame('', $stemmer->stem('', $locale));
    }

    public static function otherLocaleProvider(): \Generator
    {
        yield ['en'];
        yield ['en_US'];
        yield ['en-GB'];
        yield ['fr'];
        yield ['nl'];
    }

    public function testStemWords(): void
    {
        $this->assertSame(['frei', 'streck'], Tokenizer::tokenize('Freie Strecke', 'de', new GermanStemmer()));
    }

    public function testFeminineProfessionPlural(): void
    {
        // "-innen" is reduced to "-in" and then loses its ending like any other word
        $stem = (new GermanStemmer())->stem('lehrerinnen', 'de');

        $this->assertStringStartsWith('lehrer', $stem);
        $this->assertSame((new GermanStemmer())->stem('lehrerin', 'de'), $stem);
    }

    /**
     * @dataProvider inflectionProvider
     */
    public function testInflectionsShareStem(string ...$words): void
    {
        $stemmer = new GermanStemmer();
        $stems = \array_unique(\array_map(static fn (string $word): string => $stemmer->stem($word, 'de'), $words));

        $this->assertCount(1, $stems, 'Got different stems: ' . \implode(', ', $stems));
    }

    public static function inflectionProvider(): \Generator
    {
        yield 'Lieferung' => ['lieferung', 'lieferungen'];
        yield 'Schule' => ['schule', 'schulen'];
        yield 'Kirche' => ['kirche', 'kirchen'];
        yield 'Gruppe' => ['gruppe', 'gruppen'];
        yield 'Wanderung' => ['wanderung', 'wanderungen'];
        yield 'Zug' => ['zug', 'zuge'];
        yield 'Kind' => ['kind', 'kinder'];
        yield 'Freiheit' => ['freiheit', 'frei'];
    }

    public function testStemIsStable(): void
    {
        $stemmer = new GermanStemmer();

        $this->assertSame($stemmer->stem('lieferungen', 'de'), $stemmer->stem('lieferungen', 'de'));
        $this->assertStringStartsWith('lief', $stemmer->stem('lieferungen', 'de'));
    }
}

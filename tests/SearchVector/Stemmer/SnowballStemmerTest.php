<?php

namespace HeimrichHannot\UtilsBundle\Tests\SearchVector\Stemmer;

use HeimrichHannot\UtilsBundle\SearchVector\Stemmer\SnowballStemmer;
use HeimrichHannot\UtilsBundle\SearchVector\Stemmer\StemmerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Runs through the real wamania/php-stemmer library.
 */
class SnowballStemmerTest extends TestCase
{
    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(StemmerInterface::class, new SnowballStemmer());
    }

    /**
     * @dataProvider stemProvider
     */
    public function testStem(string $word, string $locale, string $expected): void
    {
        $this->assertSame($expected, (new SnowballStemmer())->stem($word, $locale));
    }

    public static function stemProvider(): \Generator
    {
        yield 'de plural -en' => ['führungen', 'de', 'fuhrung'];
        yield 'de plural -n' => ['schulen', 'de', 'schul'];
        yield 'de singular -e' => ['schule', 'de', 'schul'];
        yield 'de -ungen' => ['lieferungen', 'de', 'liefer'];
        yield 'de -ung' => ['wanderung', 'de', 'wander'];
        yield 'de superlative' => ['besten', 'de', 'best'];
        yield 'de umlaut plural' => ['häuser', 'de', 'haus'];
        yield 'de umlaut singular' => ['haus', 'de', 'haus'];
        yield 'de umlaut is folded by the stemmer' => ['bäckerei', 'de', 'backerei'];
        yield 'de umlaut word' => ['käse', 'de', 'kas'];
        yield 'de diminutive' => ['mädchen', 'de', 'madch'];
        yield 'de sharp s' => ['straße', 'de', 'strass'];
        yield 'de Kirchen' => ['kirchen', 'de', 'kirch'];
        yield 'de unchanged' => ['wald', 'de', 'wald'];
        yield 'de empty' => ['', 'de', ''];

        yield 'en -ing' => ['running', 'en', 'run'];
        yield 'en plural' => ['runs', 'en', 'run'];
        yield 'en houses' => ['houses', 'en', 'hous'];
        yield 'en accent is kept' => ['cafés', 'en', 'café'];

        yield 'fr plural -aux' => ['chevaux', 'fr', 'cheval'];
        yield 'fr plural -s' => ['maisons', 'fr', 'maison'];
        yield 'fr adjective' => ['nationales', 'fr', 'national'];
    }

    /**
     * @dataProvider localeVariantProvider
     */
    public function testLocaleVariantsUseTheLanguage(string $locale, string $word, string $expected): void
    {
        $this->assertSame($expected, (new SnowballStemmer())->stem($word, $locale));
    }

    public static function localeVariantProvider(): \Generator
    {
        yield ['de', 'häuser', 'haus'];
        yield ['de_DE', 'häuser', 'haus'];
        yield ['de_CH', 'häuser', 'haus'];
        yield ['de-CH', 'häuser', 'haus'];
        yield ['en', 'running', 'run'];
        yield ['en_US', 'running', 'run'];
        yield ['en-GB', 'running', 'run'];
    }

    /**
     * @dataProvider unsupportedLocaleProvider
     */
    public function testUnsupportedLocaleReturnsWordUnchanged(string $locale): void
    {
        $this->assertSame('häuser', (new SnowballStemmer())->stem('häuser', $locale));
    }

    public static function unsupportedLocaleProvider(): \Generator
    {
        yield ['xx'];
        yield ['xx_YY'];
        yield ['zh'];
        yield [''];
    }

    public function testLocaleSelectsLanguage(): void
    {
        $stemmer = new SnowballStemmer();

        $this->assertSame('schul', $stemmer->stem('schulen', 'de'));
        $this->assertSame('schulen', $stemmer->stem('schulen', 'en'));
    }

    /**
     * @dataProvider inflectionProvider
     */
    public function testInflectionsShareStem(string $locale, string ...$words): void
    {
        $stemmer = new SnowballStemmer();
        $stems = \array_unique(\array_map(static fn (string $word): string => $stemmer->stem($word, $locale), $words));

        $this->assertCount(1, $stems, 'Got different stems: ' . \implode(', ', $stems));
    }

    public static function inflectionProvider(): \Generator
    {
        yield 'Lieferung' => ['de', 'lieferung', 'lieferungen'];
        yield 'Schule' => ['de', 'schule', 'schulen', 'schulen'];
        yield 'Kirche' => ['de', 'kirche', 'kirchen'];
        yield 'Haus' => ['de', 'haus', 'häuser'];
        yield 'Kind' => ['de', 'kind', 'kinder'];
        yield 'Run' => ['en', 'run', 'runs', 'running'];
    }
}

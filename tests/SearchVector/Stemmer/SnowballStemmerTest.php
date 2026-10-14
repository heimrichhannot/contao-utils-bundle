<?php

namespace HeimrichHannot\UtilsBundle\Tests\SearchVector\Stemmer;

use HeimrichHannot\UtilsBundle\SearchVector\Stemmer\SnowballStemmer;
use HeimrichHannot\UtilsBundle\SearchVector\Stemmer\StemmerInterface;
use PHPUnit\Framework\TestCase;
use Wamania\Snowball\StemmerFactory;

/**
 * Tests the adapter against the real library. The stems themselves are the library's business and not pinned here.
 */
class SnowballStemmerTest extends TestCase
{
    private const WORDS = ['führungen', 'häuser', 'schulen', 'running', 'houses', 'maisons', 'chevaux', 'strasse', 'museum'];

    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(StemmerInterface::class, new SnowballStemmer());
    }

    /**
     * @dataProvider localeProvider
     */
    public function testUsesTheStemmerOfTheLanguageOfTheLocale(string $locale, string $language): void
    {
        $adapter = new SnowballStemmer();
        $library = StemmerFactory::create($language);

        foreach (self::WORDS as $word) {
            $this->assertSame($library->stem($word), $adapter->stem($word, $locale), \sprintf('"%s" with locale "%s"', $word, $locale));
        }
    }

    public static function localeProvider(): \Generator
    {
        yield 'de' => ['de', 'de'];
        yield 'de_DE' => ['de_DE', 'de'];
        yield 'de_CH' => ['de_CH', 'de'];
        yield 'de-CH' => ['de-CH', 'de'];
        yield 'en' => ['en', 'en'];
        yield 'en_US' => ['en_US', 'en'];
        yield 'en-GB' => ['en-GB', 'en'];
        yield 'fr' => ['fr', 'fr'];
        yield 'nl' => ['nl', 'nl'];
    }

    public function testLocaleSelectsTheLanguage(): void
    {
        $adapter = new SnowballStemmer();

        // Different languages are not mixed up, whatever the stems are
        $this->assertSame(StemmerFactory::create('de')->stem('schulen'), $adapter->stem('schulen', 'de'));
        $this->assertSame(StemmerFactory::create('en')->stem('schulen'), $adapter->stem('schulen', 'en'));
        $this->assertSame(StemmerFactory::create('fr')->stem('maisons'), $adapter->stem('maisons', 'fr'));
    }

    public function testSameInstanceServesSeveralLanguages(): void
    {
        $adapter = new SnowballStemmer();
        $german = $adapter->stem('häuser', 'de');
        $english = $adapter->stem('running', 'en');

        $this->assertSame($german, $adapter->stem('häuser', 'de_CH'));
        $this->assertSame($english, $adapter->stem('running', 'en_US'));
    }

    /**
     * @dataProvider unsupportedLocaleProvider
     */
    public function testUnsupportedLocaleReturnsWordUnchanged(string $locale): void
    {
        $this->assertSame('häuser', (new SnowballStemmer())->stem('häuser', $locale));
        $this->assertSame('führungen', (new SnowballStemmer())->stem('führungen', $locale));
    }

    public static function unsupportedLocaleProvider(): \Generator
    {
        yield ['xx'];
        yield ['xx_YY'];
        yield ['zh'];
        yield [''];
    }
}

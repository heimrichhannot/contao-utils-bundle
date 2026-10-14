<?php

namespace HeimrichHannot\UtilsBundle\Tests\SearchVector\Normalizer;

use HeimrichHannot\UtilsBundle\SearchVector\Normalizer\GermanNormalizer;
use HeimrichHannot\UtilsBundle\SearchVector\Normalizer\NormalizerInterface;
use PHPUnit\Framework\TestCase;

class GermanNormalizerTest extends TestCase
{
    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(NormalizerInterface::class, new GermanNormalizer());
    }

    /**
     * @dataProvider normalizeProvider
     */
    public function testNormalize(string $word, string $expected): void
    {
        $this->assertSame($expected, (new GermanNormalizer())->normalize($word, 'de'));
    }

    public static function normalizeProvider(): \Generator
    {
        // ae, oe and ue are restored to umlauts
        yield 'muenchen' => ['muenchen', 'münchen'];
        yield 'koeln' => ['koeln', 'köln'];
        yield 'haeuser' => ['haeuser', 'häuser'];
        yield 'aerzte' => ['aerzte', 'ärzte'];
        yield 'uebel' => ['uebel', 'übel'];
        yield 'compound' => ['baden-oeffingen', 'baden-öffingen'];

        // ue after a vowel or q is no umlaut
        yield 'feuer' => ['feuer', 'feuer'];
        yield 'bauer' => ['bauer', 'bauer'];
        yield 'quelle' => ['quelle', 'quelle'];
        yield 'neue' => ['neue', 'neue'];
        yield 'steuer' => ['steuer', 'steuer'];
        yield 'idee' => ['idee', 'idee'];
        yield 'boot' => ['boot', 'boot'];

        // known false positives of the rules, same as in Lucene
        yield 'goethe' => ['goethe', 'göthe'];
        yield 'aktuell' => ['aktuell', 'aktüll'];
        yield 'israel' => ['israel', 'isräl'];

        // already normalized
        yield 'münchen' => ['münchen', 'münchen'];
        yield 'straße' => ['straße', 'straße'];
        yield 'plain word' => ['wald', 'wald'];
        yield 'empty' => ['', ''];
    }

    /**
     * @dataProvider germanLocaleProvider
     */
    public function testGermanLocales(string $locale): void
    {
        $this->assertSame('münchen', (new GermanNormalizer())->normalize('muenchen', $locale));
    }

    public static function germanLocaleProvider(): \Generator
    {
        yield ['de'];
        yield ['de_DE'];
        yield ['de_AT'];
        yield ['de_CH'];
        yield ['de-CH'];
    }

    /**
     * @dataProvider otherLocaleProvider
     */
    public function testOtherLocalesAreUnchanged(string $locale): void
    {
        $this->assertSame('muenchen', (new GermanNormalizer())->normalize('muenchen', $locale));
        $this->assertSame('haeuser', (new GermanNormalizer())->normalize('haeuser', $locale));
    }

    public static function otherLocaleProvider(): \Generator
    {
        yield ['en'];
        yield ['en_US'];
        yield ['fr'];
        yield [''];
    }

    /**
     * @dataProvider normalizeProvider
     */
    public function testIsIdempotent(string $word): void
    {
        $normalizer = new GermanNormalizer();
        $once = $normalizer->normalize($word, 'de');

        $this->assertSame($once, $normalizer->normalize($once, 'de'));
    }
}

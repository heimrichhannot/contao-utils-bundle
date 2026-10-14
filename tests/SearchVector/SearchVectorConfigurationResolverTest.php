<?php

namespace HeimrichHannot\UtilsBundle\Tests\SearchVector;

use Contao\Controller;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Dca\SearchVectorField;
use HeimrichHannot\UtilsBundle\SearchVector\SearchVectorConfigurationResolver;
use HeimrichHannot\UtilsBundle\SearchVector\Normalizer\GermanNormalizer;
use HeimrichHannot\UtilsBundle\Tests\SearchVector\Stemmer\SuffixTestStemmer;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SearchVectorConfigurationResolverTest extends ContaoTestCase
{
    private function createResolver(?RequestStack $requestStack = null, array $stemmers = [], array $normalizers = []): SearchVectorConfigurationResolver
    {
        $controller = $this->mockAdapter(['loadDataContainer']);
        $controller->method('loadDataContainer');

        return new SearchVectorConfigurationResolver(
            $this->mockContaoFramework([Controller::class => $controller]),
            new ServiceLocator($stemmers),
            new ServiceLocator($normalizers),
            $requestStack ?? new RequestStack(),
            'de'
        );
    }

    private function createRequestStack(string $locale): RequestStack
    {
        $request = new Request();
        $request->setLocale($locale);
        $requestStack = new RequestStack();
        $requestStack->push($request);

        return $requestStack;
    }

    public function testGetConfiguration(): void
    {
        $configuration = SearchVectorField::register('tl_resolver_registered');

        $this->assertSame($configuration, $this->createResolver()->getConfiguration('tl_resolver_registered'));
    }

    public function testGetConfigurationOfUnregisteredTableThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Table "tl_resolver_unregistered" has no search vector field.');

        $this->createResolver()->getConfiguration('tl_resolver_unregistered');
    }

    public function testGetStemmerIsNullIfDisabled(): void
    {
        $configuration = SearchVectorField::register('tl_resolver_stemmer_null')->setStemmer(null);

        $this->assertNull($this->createResolver()->getStemmer($configuration));
    }

    public function testGetStemmerReturnsRegisteredService(): void
    {
        $stemmer = new SuffixTestStemmer();
        $configuration = SearchVectorField::register('tl_resolver_stemmer')->setStemmer(SuffixTestStemmer::class);
        $resolver = $this->createResolver(null, [SuffixTestStemmer::class => static fn (): SuffixTestStemmer => $stemmer]);

        $this->assertSame($stemmer, $resolver->getStemmer($configuration));
    }

    public function testGetStemmerWithUnknownServiceIdThrows(): void
    {
        $configuration = SearchVectorField::register('tl_resolver_stemmer_unknown')->setStemmer('App\\UnknownStemmer');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The search vector stemmer "App\\UnknownStemmer" of table "tl_resolver_stemmer_unknown" is not a registered service implementing');

        $this->createResolver()->getStemmer($configuration);
    }

    public function testGetStemmerWithServiceOfWrongTypeThrows(): void
    {
        $configuration = SearchVectorField::register('tl_resolver_stemmer_wrong_type')->setStemmer('app.not_a_stemmer');
        $resolver = $this->createResolver(null, ['app.not_a_stemmer' => static fn (): \stdClass => new \stdClass()]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The search vector stemmer "app.not_a_stemmer" of table "tl_resolver_stemmer_wrong_type" is not a registered service implementing HeimrichHannot\\UtilsBundle\\SearchVector\\Stemmer\\StemmerInterface.');

        $resolver->getStemmer($configuration);
    }

    public function testGetNormalizerIsNullByDefault(): void
    {
        $configuration = SearchVectorField::register('tl_resolver_normalizer_null');

        $this->assertNull($this->createResolver()->getNormalizer($configuration));
    }

    public function testGetNormalizerReturnsRegisteredService(): void
    {
        $normalizer = new GermanNormalizer();
        $configuration = SearchVectorField::register('tl_resolver_normalizer')->setNormalizer(GermanNormalizer::class);
        $resolver = $this->createResolver(null, [], [GermanNormalizer::class => static fn (): GermanNormalizer => $normalizer]);

        $this->assertSame($normalizer, $resolver->getNormalizer($configuration));
    }

    public function testGetNormalizerWithUnknownServiceIdThrows(): void
    {
        $configuration = SearchVectorField::register('tl_resolver_normalizer_unknown')->setNormalizer('App\\UnknownNormalizer');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The search vector normalizer "App\\UnknownNormalizer" of table "tl_resolver_normalizer_unknown" is not a registered service implementing');

        $this->createResolver()->getNormalizer($configuration);
    }

    public function testGetNormalizerWithServiceOfWrongTypeThrows(): void
    {
        $configuration = SearchVectorField::register('tl_resolver_normalizer_wrong_type')->setNormalizer(SuffixTestStemmer::class);
        $resolver = $this->createResolver(null, [], [SuffixTestStemmer::class => static fn (): SuffixTestStemmer => new SuffixTestStemmer()]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The search vector normalizer "HeimrichHannot\\UtilsBundle\\Tests\\SearchVector\\Stemmer\\SuffixTestStemmer" of table "tl_resolver_normalizer_wrong_type" is not a registered service implementing HeimrichHannot\\UtilsBundle\\SearchVector\\Normalizer\\NormalizerInterface.');

        $resolver->getNormalizer($configuration);
    }

    public function testNormalizerAndStemmerAreLookedUpSeparately(): void
    {
        $configuration = SearchVectorField::register('tl_resolver_separate')->setNormalizer(GermanNormalizer::class);
        // only registered as stemmer
        $resolver = $this->createResolver(null, [GermanNormalizer::class => static fn (): GermanNormalizer => new GermanNormalizer()]);

        $this->expectException(\InvalidArgumentException::class);

        $resolver->getNormalizer($configuration);
    }

    public function testGetRecordLocale(): void
    {
        $resolver = $this->createResolver($this->createRequestStack('fr'));

        // default locale, the request is never used
        $configuration = SearchVectorField::register('tl_resolver_record_default');
        $this->assertSame('de', $resolver->getRecordLocale($configuration, ['language' => 'en']));

        // configured language
        $configuration->setLanguage('en_GB');
        $this->assertSame('en_GB', $resolver->getRecordLocale($configuration, []));

        // language field value
        $configuration->setLanguageField('language');
        $this->assertSame('nl', $resolver->getRecordLocale($configuration, ['language' => 'nl']));

        // empty or missing field value falls back to the configured language
        $this->assertSame('en_GB', $resolver->getRecordLocale($configuration, ['language' => '']));
        $this->assertSame('en_GB', $resolver->getRecordLocale($configuration, ['language' => null]));
        $this->assertSame('en_GB', $resolver->getRecordLocale($configuration, []));

        // ... and without configured language to the default locale
        $configuration->setLanguage(null);
        $this->assertSame('de', $resolver->getRecordLocale($configuration, ['language' => '']));
    }

    public function testGetSearchLocaleExplicitLocaleWins(): void
    {
        $resolver = $this->createResolver($this->createRequestStack('fr'));

        $configuration = SearchVectorField::register('tl_resolver_search_explicit')->setLanguage('en');
        $this->assertSame('nl', $resolver->getSearchLocale($configuration, 'nl'));

        $configuration->setLanguageField('language');
        $this->assertSame('nl', $resolver->getSearchLocale($configuration, 'nl'));
    }

    public function testGetSearchLocaleWithoutLanguageFieldIgnoresRequest(): void
    {
        $resolver = $this->createResolver($this->createRequestStack('fr'));

        $configuration = SearchVectorField::register('tl_resolver_search_no_field');
        $this->assertSame('de', $resolver->getSearchLocale($configuration));

        $configuration->setLanguage('en');
        $this->assertSame('en', $resolver->getSearchLocale($configuration));
    }

    public function testGetSearchLocaleWithLanguageFieldUsesRequest(): void
    {
        $resolver = $this->createResolver($this->createRequestStack('fr'));

        $configuration = SearchVectorField::register('tl_resolver_search_field')->setLanguageField('language')->setLanguage('en');
        $this->assertSame('fr', $resolver->getSearchLocale($configuration));
    }

    public function testGetSearchLocaleWithLanguageFieldWithoutRequestFallsBack(): void
    {
        $resolver = $this->createResolver();

        $configuration = SearchVectorField::register('tl_resolver_search_no_request')->setLanguageField('language');
        $this->assertSame('de', $resolver->getSearchLocale($configuration));

        $configuration->setLanguage('en');
        $this->assertSame('en', $resolver->getSearchLocale($configuration));
    }
}

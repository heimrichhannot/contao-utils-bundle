<?php

namespace HeimrichHannot\UtilsBundle\Tests\Dca;

use HeimrichHannot\UtilsBundle\Dca\DateAddedField;
use HeimrichHannot\UtilsBundle\Dca\SearchVectorField;
use HeimrichHannot\UtilsBundle\Dca\SearchVectorFieldConfiguration;
use PHPUnit\Framework\TestCase;

class SearchVectorFieldTest extends TestCase
{
    public function testRegisterReturnsConfigurationWithDefaults(): void
    {
        $config = SearchVectorField::register('tl_search_vector_defaults');

        $this->assertInstanceOf(SearchVectorFieldConfiguration::class, $config);
        $this->assertSame('tl_search_vector_defaults', $config->getTable());
        $this->assertSame('searchVector', $config->getFieldName());
        $this->assertNull($config->getStemmer());
        $this->assertTrue($config->isExclude());
        $this->assertSame([], $config->getFields());
        $this->assertNull($config->getNormalizer());
        $this->assertNull($config->getLanguage());
        $this->assertNull($config->getLanguageField());
    }

    public function testSetters(): void
    {
        $config = SearchVectorField::register('tl_search_vector_setters');

        $this->assertSame($config, $config->setFields(['title', 'a' => 'teaser']));
        $this->assertSame($config, $config->setFieldName('myVector'));
        $this->assertSame($config, $config->setStemmer(null));

        $this->assertSame(['title', 'teaser'], $config->getFields());
        $this->assertSame('myVector', $config->getFieldName());
        $this->assertNull($config->getStemmer());

        $this->assertSame($config, $config->setNormalizer('App\\MyNormalizer'));
        $this->assertSame('App\\MyNormalizer', $config->getNormalizer());
        $config->setNormalizer(null);
        $this->assertNull($config->getNormalizer());

        $this->assertSame($config, $config->setLanguage('en'));
        $this->assertSame($config, $config->setLanguageField('language'));
        $this->assertSame('en', $config->getLanguage());
        $this->assertSame('language', $config->getLanguageField());

        $config->setLanguage(null)->setLanguageField(null);
        $this->assertNull($config->getLanguage());
        $this->assertNull($config->getLanguageField());

        $config->setStemmer('App\\MyStemmer');
        $this->assertSame('App\\MyStemmer', $config->getStemmer());
    }

    public function testGetRegistrationsAreKeyedByTable(): void
    {
        $first = SearchVectorField::register('tl_search_vector_first');
        $second = SearchVectorField::register('tl_search_vector_second');

        $registrations = SearchVectorField::getRegistrations();

        $this->assertSame($first, $registrations['tl_search_vector_first']);
        $this->assertSame($second, $registrations['tl_search_vector_second']);
    }

    public function testRegisterTwiceReplacesConfiguration(): void
    {
        $first = SearchVectorField::register('tl_search_vector_twice');
        $second = SearchVectorField::register('tl_search_vector_twice');

        $this->assertNotSame($first, $second);
        $this->assertSame($second, SearchVectorField::getRegistrations()['tl_search_vector_twice']);
    }

    public function testRegistryIsSeparateFromOtherDcaFields(): void
    {
        SearchVectorField::register('tl_search_vector_separate');

        $this->assertArrayNotHasKey('tl_search_vector_separate', DateAddedField::getRegistrations());
    }
}

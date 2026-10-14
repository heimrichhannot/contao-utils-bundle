<?php

namespace HeimrichHannot\UtilsBundle\Tests\SearchVector;

use Doctrine\DBAL\Connection;
use Contao\Controller;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\DefaultSchemaManagerFactory;
use HeimrichHannot\UtilsBundle\Dca\SearchVectorField;
use HeimrichHannot\UtilsBundle\Dca\SearchVectorFieldConfiguration;
use HeimrichHannot\UtilsBundle\SearchVector\Normalizer\GermanNormalizer;
use HeimrichHannot\UtilsBundle\SearchVector\SearchVectorConfigurationResolver;
use HeimrichHannot\UtilsBundle\SearchVector\SearchVectorQuery;
use HeimrichHannot\UtilsBundle\Tests\SearchVector\Stemmer\SuffixTestStemmer;
use HeimrichHannot\UtilsBundle\SearchVector\Stemmer\StemmerInterface;
use HeimrichHannot\UtilsBundle\SearchVector\Tokenizer;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SearchVectorQueryTest extends ContaoTestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true], $this->createDbalConfiguration());
    }

    /**
     * Uses a real resolver with the table registered as in a DCA file.
     *
     * @param callable(SearchVectorFieldConfiguration): mixed|null $configure
     */
    private function createQuery(
        ?StemmerInterface $stemmer = null,
        string $fieldName = 'searchVector',
        ?callable $configure = null,
        ?RequestStack $requestStack = null,
    ): SearchVectorQuery {
        $stemmerId = match (true) {
            null === $stemmer => null,
            $stemmer instanceof SuffixTestStemmer => SuffixTestStemmer::class,
            default => 'test.stemmer',
        };

        $configuration = SearchVectorField::register('tl_example')
            ->setFieldName($fieldName)
            ->setStemmer($stemmerId);

        if ($configure) {
            $configure($configuration);
        }

        $controller = $this->mockAdapter(['loadDataContainer']);
        $controller->method('loadDataContainer');

        $resolver = new SearchVectorConfigurationResolver(
            $this->mockContaoFramework([Controller::class => $controller]),
            new ServiceLocator($stemmerId ? [$stemmerId => static fn (): StemmerInterface => $stemmer] : []),
            new ServiceLocator([GermanNormalizer::class => static fn (): GermanNormalizer => new GermanNormalizer()]),
            $requestStack ?? new RequestStack(),
            'de'
        );

        return new SearchVectorQuery($this->connection, $resolver);
    }

    private function createStemmerStub(array $map): StemmerInterface
    {
        return new class($map) implements StemmerInterface {
            public function __construct(private readonly array $map) {}

            public function stem(string $word, string $locale): string
            {
                return $this->map[$word] ?? $word;
            }
        };
    }

    public function testApplySingleWord(): void
    {
        $qb = $this->connection->createQueryBuilder()->select('*')->from('tl_example');

        $this->assertTrue($this->createQuery(new SuffixTestStemmer())->apply($qb, 'tl_example', 'Führungen'));

        $this->assertSame(
            'SELECT * FROM tl_example WHERE "tl_example"."searchVector" LIKE :dcValue1 ESCAPE \'!\'',
            $qb->getSQL()
        );
        $this->assertSame(['dcValue1' => '%fuhr%'], $qb->getParameters());
    }

    public function testApplyMultipleWordsAreCombinedWithAnd(): void
    {
        $qb = $this->connection->createQueryBuilder()->select('*')->from('tl_example');

        $this->createQuery(new SuffixTestStemmer())->apply($qb, 'tl_example', 'führungen museum');

        $this->assertSame(
            'SELECT * FROM tl_example WHERE ("tl_example"."searchVector" LIKE :dcValue1 ESCAPE \'!\') AND ("tl_example"."searchVector" LIKE :dcValue2 ESCAPE \'!\')',
            $qb->getSQL()
        );
        $this->assertSame(['dcValue1' => '%fuhr%', 'dcValue2' => '%museum%'], $qb->getParameters());
    }

    public function testApplyCommaPhrasesAreCombinedWithOr(): void
    {
        $qb = $this->connection->createQueryBuilder()->select('*')->from('tl_example');

        $this->createQuery(new SuffixTestStemmer())->apply($qb, 'tl_example', 'führungen museum, kind');

        $this->assertSame(
            'SELECT * FROM tl_example WHERE (("tl_example"."searchVector" LIKE :dcValue1 ESCAPE \'!\') AND ("tl_example"."searchVector" LIKE :dcValue2 ESCAPE \'!\')) OR ("tl_example"."searchVector" LIKE :dcValue3 ESCAPE \'!\')',
            $qb->getSQL()
        );
        $this->assertSame(['dcValue1' => '%fuhr%', 'dcValue2' => '%museum%', 'dcValue3' => '%kind%'], $qb->getParameters());
    }

    public function testApplyQuotedPhraseIsSingleTerm(): void
    {
        $qb = $this->connection->createQueryBuilder()->select('*')->from('tl_example');

        $this->createQuery(new SuffixTestStemmer())->apply($qb, 'tl_example', '"bei Nacht"');

        $this->assertSame('SELECT * FROM tl_example WHERE "tl_example"."searchVector" LIKE :dcValue1 ESCAPE \'!\'', $qb->getSQL());
        $this->assertSame(['dcValue1' => '%bei nacht%'], $qb->getParameters());
    }

    public function testApplyUsesDefaultLocaleOfResolver(): void
    {
        $qb = $this->connection->createQueryBuilder()->select('*')->from('tl_example');

        $this->createQuery(new SuffixTestStemmer())->apply($qb, 'tl_example', 'Schulen');

        $this->assertSame(['dcValue1' => '%schul%'], $qb->getParameters());
    }

    public function testApplyUsesConfiguredLanguage(): void
    {
        $qb = $this->connection->createQueryBuilder()->select('*')->from('tl_example');

        $this->createQuery(new SuffixTestStemmer(), 'searchVector', static fn (SearchVectorFieldConfiguration $c) => $c->setLanguage('en'))
            ->apply($qb, 'tl_example', 'Schulen');

        $this->assertSame(['dcValue1' => '%schulen%'], $qb->getParameters());
    }

    public function testApplyWithExplicitLocaleDisablesGermanStemming(): void
    {
        $query = $this->createQuery(new SuffixTestStemmer());

        $german = $this->connection->createQueryBuilder()->select('*')->from('tl_example');
        $query->apply($german, 'tl_example', 'Führungen Museum, "bei Nacht"', null, 'de_CH');

        $english = $this->connection->createQueryBuilder()->select('*')->from('tl_example');
        $query->apply($english, 'tl_example', 'Führungen Museum, "bei Nacht"', null, 'en');

        $this->assertSame(['dcValue1' => '%fuhr%', 'dcValue2' => '%museum%', 'dcValue3' => '%bei nacht%'], $german->getParameters());
        $this->assertSame(['dcValue1' => '%fuhrungen%', 'dcValue2' => '%museum%', 'dcValue3' => '%bei nacht%'], $english->getParameters());
    }

    public function testApplyUsesRequestLocaleForTablesWithLanguageField(): void
    {
        $requestStack = new RequestStack();
        $request = new Request();
        $request->setLocale('en');
        $requestStack->push($request);

        $query = $this->createQuery(
            new SuffixTestStemmer(),
            'searchVector',
            static fn (SearchVectorFieldConfiguration $c) => $c->setLanguageField('language')->setLanguage('de'),
            $requestStack
        );

        $qb = $this->connection->createQueryBuilder()->select('*')->from('tl_example');
        $query->apply($qb, 'tl_example', 'Schulen');
        $this->assertSame(['dcValue1' => '%schulen%'], $qb->getParameters());

        // explicit locale wins over the request
        $qb = $this->connection->createQueryBuilder()->select('*')->from('tl_example');
        $query->apply($qb, 'tl_example', 'Schulen', null, 'de');
        $this->assertSame(['dcValue1' => '%schul%'], $qb->getParameters());
    }

    public function testApplyIgnoresRequestLocaleForTablesWithoutLanguageField(): void
    {
        $requestStack = new RequestStack();
        $request = new Request();
        $request->setLocale('en');
        $requestStack->push($request);

        $qb = $this->connection->createQueryBuilder()->select('*')->from('tl_example');
        $this->createQuery(new SuffixTestStemmer(), 'searchVector', null, $requestStack)->apply($qb, 'tl_example', 'Schulen');

        $this->assertSame(['dcValue1' => '%schul%'], $qb->getParameters());
    }

    public function testApplyUsesAliasAndFieldName(): void
    {
        $qb = $this->connection->createQueryBuilder()->select('*')->from('tl_example', 'e');

        $this->createQuery(null, 'myVector')->apply($qb, 'tl_example', 'Museum', 'e');

        $this->assertSame('SELECT * FROM tl_example e WHERE "e"."myVector" LIKE :dcValue1 ESCAPE \'!\'', $qb->getSQL());
        $this->assertSame(['dcValue1' => '%museum%'], $qb->getParameters());
    }

    public function testApplyWithoutStemmer(): void
    {
        $qb = $this->connection->createQueryBuilder()->select('*')->from('tl_example');

        $this->createQuery(null)->apply($qb, 'tl_example', 'Führungen');

        $this->assertSame(['dcValue1' => '%fuhrungen%'], $qb->getParameters());
    }

    public function testApplyKeepsExistingConditions(): void
    {
        $qb = $this->connection->createQueryBuilder()->select('*')->from('tl_example')->where('published = 1');

        $this->createQuery(null)->apply($qb, 'tl_example', 'museum');

        $this->assertSame(
            'SELECT * FROM tl_example WHERE (published = 1) AND ("tl_example"."searchVector" LIKE :dcValue1 ESCAPE \'!\')',
            $qb->getSQL()
        );
    }

    public function testApplyEscapesLikeWildcards(): void
    {
        // The tokenizer strips wildcards, so a stemmer has to produce them
        $stemmer = $this->createStemmerStub(['foo' => '50%_off\\x!y']);
        $qb = $this->connection->createQueryBuilder()->select('*')->from('tl_example');

        $this->assertTrue($this->createQuery($stemmer)->apply($qb, 'tl_example', 'foo'));

        $this->assertSame(['dcValue1' => '%50!%!_off\\x!!y%'], $qb->getParameters());
    }

    public function testEscapedWildcardsMatchLiterally(): void
    {
        $this->createTable([
            1 => '| 50%_off!y |',
            2 => '| 50x-off!y |',
            3 => '| 50%_offy |',
        ]);

        // The tokenizer strips wildcards, so a stemmer has to produce them
        $query = $this->createQuery($this->createStemmerStub(['foo' => '50%_off!y']));

        $this->assertSame([1], $this->search($query, 'foo'));

        [$columns, $values] = $query->createModelCriteria('tl_example', 'foo');
        $this->assertSame([1], \array_map('intval', $this->connection->fetchFirstColumn('SELECT id FROM tl_example WHERE ' . \implode(' AND ', $columns), $values)));
    }

    public function testApplyUserInputCannotInjectSql(): void
    {
        $qb = $this->connection->createQueryBuilder()->select('*')->from('tl_example');

        $this->createQuery(null)->apply($qb, 'tl_example', "x' OR 1=1 --");

        $this->assertStringNotContainsString('1=1', $qb->getSQL());
        $this->assertSame(['dcValue1' => '%x%', 'dcValue2' => '%or%', 'dcValue3' => '%1%'], $qb->getParameters());
    }

    /**
     * @dataProvider emptySearchProvider
     */
    public function testApplyWithEmptySearchLeavesQueryUntouched(string $search): void
    {
        $qb = $this->connection->createQueryBuilder()->select('*')->from('tl_example')->where('published = 1');
        $sql = $qb->getSQL();

        $this->assertFalse($this->createQuery(new SuffixTestStemmer())->apply($qb, 'tl_example', $search));

        $this->assertSame($sql, $qb->getSQL());
        $this->assertSame([], $qb->getParameters());
    }

    public static function emptySearchProvider(): \Generator
    {
        yield 'empty' => [''];
        yield 'whitespace' => ['   '];
        yield 'punctuation' => [' , ;; "" - '];
    }

    public function testApplyFindsMatchingRows(): void
    {
        $this->createTable([
            1 => '| stadtmuseum am rathaus | somm | stadtfuhr |',
            2 => '| stadtrundgang | koln |',
            3 => '| baden-wurttemberg | liefer nach munch |',
            4 => '',
        ]);
        $query = $this->createQuery(new SuffixTestStemmer());

        // single word, matched as substring of a compound
        $this->assertSame([1], $this->search($query, 'Stadtmuseum'));
        $this->assertSame([1], $this->search($query, 'museum'));
        $this->assertSame([3], $this->search($query, 'Württemberg'));
        // inflection and umlaut
        $this->assertSame([3], $this->search($query, 'Lieferung München'));
        // AND
        $this->assertSame([1], $this->search($query, 'sommer stadtführungen'));
        $this->assertSame([], $this->search($query, 'sommer rundgänge'));
        // OR
        $this->assertSame([1, 2], $this->search($query, 'sommer, rundgänge'));
        $this->assertSame([1, 2, 3], $this->search($query, 'sommer, köln, württemberg'));
        // phrase
        $this->assertSame([1], $this->search($query, '"am Rathaus"'));
        $this->assertSame([], $this->search($query, '"Rathaus am"'));
        // word and phrase
        $this->assertSame([1], $this->search($query, 'sommer "am Rathaus"'));
        $this->assertSame([], $this->search($query, 'rundgänge "am Rathaus"'));
        $this->assertSame([1, 2], $this->search($query, 'köln, sommer "am Rathaus"'));
        // pipe separator keeps a phrase from matching across values
        $this->assertSame([], $this->search($query, '"rathaus sommer"'));
        // no match
        $this->assertSame([], $this->search($query, 'hamburg'));
    }

    public function testNormalizerUnifiesUmlautSpellings(): void
    {
        $normalizer = new GermanNormalizer();
        $stemmer = new SuffixTestStemmer();

        // the vector of a record is built the way the indexer does it
        $this->createTable([
            1 => '| ' . \implode(' ', Tokenizer::tokenize('Lieferungen nach München', 'de', $stemmer, $normalizer)) . ' |',
            2 => '| ' . \implode(' ', Tokenizer::tokenize('Feuer und Quelle', 'de', $stemmer, $normalizer)) . ' |',
        ]);
        $this->assertSame('| liefer nach munch |', $this->connection->fetchOne('SELECT searchVector FROM tl_example WHERE id = 1'));

        $query = $this->createQuery($stemmer, 'searchVector', static fn (SearchVectorFieldConfiguration $c) => $c->setNormalizer(GermanNormalizer::class));

        foreach (['Muenchen', 'München', 'Munchen', 'MUENCHEN'] as $search) {
            $this->assertSame([1], $this->search($query, $search), $search);
        }

        $this->assertSame([1], $this->search($query, '"nach Muenchen"'));
        $this->assertSame([2], $this->search($query, 'Feuer'));
        $this->assertSame([], $this->search($query, 'Hamburg'));
    }

    public function testWithoutNormalizerUmlautSpellingIsNotUnified(): void
    {
        $stemmer = new SuffixTestStemmer();
        $this->createTable([1 => '| ' . \implode(' ', Tokenizer::tokenize('München', 'de', $stemmer)) . ' |']);

        $query = $this->createQuery($stemmer);

        $this->assertSame([1], $this->search($query, 'München'));
        $this->assertSame([1], $this->search($query, 'Munchen'));
        $this->assertSame([], $this->search($query, 'Muenchen'));
    }

    public function testApplyPassesNormalizerToQueryTerms(): void
    {
        $qb = $this->connection->createQueryBuilder()->select('*')->from('tl_example');

        $this->createQuery(null, 'searchVector', static fn (SearchVectorFieldConfiguration $c) => $c->setNormalizer(GermanNormalizer::class))
            ->apply($qb, 'tl_example', 'Muenchen');

        $this->assertSame(['dcValue1' => '%munchen%'], $qb->getParameters());
    }

    public function testCreateModelCriteriaPassesNormalizer(): void
    {
        $query = $this->createQuery(null, 'searchVector', static fn (SearchVectorFieldConfiguration $c) => $c->setNormalizer(GermanNormalizer::class));

        $this->assertSame(['%munchen%'], $query->createModelCriteria('tl_example', 'Muenchen')[1]);
        $this->assertSame(['%muenchen%'], $query->createModelCriteria('tl_example', 'Muenchen', 'en')[1]);
    }

    public function testApplyWithAliasAndJoinOnRealQuery(): void
    {
        $this->createTable([1 => '| sommer |', 2 => '| winter |']);

        $qb = $this->connection->createQueryBuilder()->select('e.id')->from('tl_example', 'e');
        $this->createQuery(null)->apply($qb, 'tl_example', 'Winter', 'e');

        $this->assertSame([2], \array_map('intval', $qb->executeQuery()->fetchFirstColumn()));
    }

    public function testCreateModelCriteria(): void
    {
        [$columns, $values] = $this->createQuery(new SuffixTestStemmer())->createModelCriteria('tl_example', 'führungen museum, "bei Nacht"');

        $this->assertSame(
            ['(("tl_example"."searchVector" LIKE ? ESCAPE \'!\' AND "tl_example"."searchVector" LIKE ? ESCAPE \'!\') OR ("tl_example"."searchVector" LIKE ? ESCAPE \'!\'))'],
            $columns
        );
        $this->assertSame(['%fuhr%', '%museum%', '%bei nacht%'], $values);
    }

    public function testCreateModelCriteriaWithExplicitLocale(): void
    {
        $query = $this->createQuery(new SuffixTestStemmer());

        $this->assertSame(['%schul%'], $query->createModelCriteria('tl_example', 'Schulen')[1]);
        $this->assertSame(['%schul%'], $query->createModelCriteria('tl_example', 'Schulen', 'de')[1]);
        $this->assertSame(['%schulen%'], $query->createModelCriteria('tl_example', 'Schulen', 'en')[1]);
    }

    public function testCreateModelCriteriaSingleWord(): void
    {
        [$columns, $values] = $this->createQuery(null)->createModelCriteria('tl_example', 'Museum');

        $this->assertSame(['(("tl_example"."searchVector" LIKE ? ESCAPE \'!\'))'], $columns);
        $this->assertSame(['%museum%'], $values);
    }

    public function testCreateModelCriteriaEscapesLikeWildcards(): void
    {
        $stemmer = $this->createStemmerStub(['foo' => 'a%b_c']);

        [, $values] = $this->createQuery($stemmer)->createModelCriteria('tl_example', 'foo');

        $this->assertSame(['%a!%b!_c%'], $values);
    }

    public function testCreateModelCriteriaWithEmptySearch(): void
    {
        $this->assertSame([[], []], $this->createQuery(new SuffixTestStemmer())->createModelCriteria('tl_example', ' , "" '));
    }

    public function testCreateCondition(): void
    {
        [$condition, $parameters] = $this->createQuery(new SuffixTestStemmer())->createCondition('tl_example', 'führungen museum, "bei Nacht"');

        $this->assertSame(
            '(("tl_example"."searchVector" LIKE :searchVector_0 ESCAPE \'!\' AND "tl_example"."searchVector" LIKE :searchVector_1 ESCAPE \'!\') OR ("tl_example"."searchVector" LIKE :searchVector_2 ESCAPE \'!\'))',
            $condition
        );
        $this->assertSame(['searchVector_0' => '%fuhr%', 'searchVector_1' => '%museum%', 'searchVector_2' => '%bei nacht%'], $parameters);
    }

    public function testCreateConditionUsesAliasFieldNameAndPrefix(): void
    {
        [$condition, $parameters] = $this->createQuery(null, 'myVector')->createCondition('tl_example', 'Museum', 'e', parameterPrefix: 'sv');

        $this->assertSame('(("e"."myVector" LIKE :sv_0 ESCAPE \'!\'))', $condition);
        $this->assertSame(['sv_0' => '%museum%'], $parameters);
    }

    public function testCreateConditionWithExplicitLocale(): void
    {
        $query = $this->createQuery(new SuffixTestStemmer());

        $this->assertSame(['searchVector_0' => '%schul%'], $query->createCondition('tl_example', 'Schulen')[1]);
        $this->assertSame(['searchVector_0' => '%schulen%'], $query->createCondition('tl_example', 'Schulen', locale: 'en')[1]);
    }

    public function testCreateConditionEscapesLikeWildcards(): void
    {
        $stemmer = $this->createStemmerStub(['foo' => 'a%b_c']);

        $this->assertSame(['searchVector_0' => '%a!%b!_c%'], $this->createQuery($stemmer)->createCondition('tl_example', 'foo')[1]);
    }

    /**
     * @dataProvider emptySearchProvider
     */
    public function testCreateConditionWithEmptySearch(string $search): void
    {
        $this->assertNull($this->createQuery(new SuffixTestStemmer())->createCondition('tl_example', $search));
    }

    public function testCreateConditionRejectsInvalidParameterPrefix(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->createQuery(null)->createCondition('tl_example', 'museum', parameterPrefix: 'a:b');
    }

    public function testCreateConditionFindsMatchingRowsOnRealQuery(): void
    {
        $this->createTable([1 => '| stadtmuseum | sommer |', 2 => '| winter | museum |', 3 => '| bei nacht |']);

        [$condition, $parameters] = $this->createQuery(null)->createCondition('tl_example', 'museum winter, "bei nacht"', 'e');

        $this->assertSame([2, 3], \array_map('intval', $this->connection->fetchFirstColumn(
            'SELECT e.id FROM tl_example e WHERE e.published = 1 AND ' . $condition . ' ORDER BY e.id',
            $parameters
        )));
    }

    private function createTable(array $vectors): void
    {
        $this->connection->executeStatement('CREATE TABLE tl_example (id INTEGER PRIMARY KEY, published INTEGER DEFAULT 1, searchVector TEXT)');

        foreach ($vectors as $id => $vector) {
            $this->connection->insert('tl_example', ['id' => $id, 'searchVector' => $vector]);
        }
    }

    /**
     * @return list<int>
     */
    private function search(SearchVectorQuery $query, string $search): array
    {
        $qb = $this->connection->createQueryBuilder()->select('id')->from('tl_example')->orderBy('id');

        if (!$query->apply($qb, 'tl_example', $search)) {
            $this->fail('Search was not applied: ' . $search);
        }

        return \array_map('intval', $qb->executeQuery()->fetchFirstColumn());
    }

    private function createDbalConfiguration(): Configuration
    {
        // Leaving the schema manager factory unset is deprecated
        $configuration = new Configuration();
        $configuration->setSchemaManagerFactory(new DefaultSchemaManagerFactory());

        return $configuration;
    }
}

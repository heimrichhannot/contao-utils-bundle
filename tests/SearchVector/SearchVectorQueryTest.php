<?php

namespace HeimrichHannot\UtilsBundle\Tests\SearchVector;

use Doctrine\DBAL\Connection;
use Contao\Controller;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\DriverManager;
use HeimrichHannot\UtilsBundle\Dca\SearchVectorField;
use HeimrichHannot\UtilsBundle\Dca\SearchVectorFieldConfiguration;
use HeimrichHannot\UtilsBundle\SearchVector\SearchVectorConfigurationResolver;
use HeimrichHannot\UtilsBundle\SearchVector\SearchVectorQuery;
use HeimrichHannot\UtilsBundle\SearchVector\Stemmer\SnowballStemmer;
use HeimrichHannot\UtilsBundle\SearchVector\Stemmer\StemmerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SearchVectorQueryTest extends ContaoTestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
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
            $stemmer instanceof SnowballStemmer => SnowballStemmer::class,
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

        $this->assertTrue($this->createQuery(new SnowballStemmer())->apply($qb, 'tl_example', 'Weihnachten'));

        $this->assertSame(
            'SELECT * FROM tl_example WHERE "tl_example"."searchVector" LIKE :dcValue1',
            $qb->getSQL()
        );
        $this->assertSame(['dcValue1' => '%weihnacht%'], $qb->getParameters());
    }

    public function testApplyMultipleWordsAreCombinedWithAnd(): void
    {
        $qb = $this->connection->createQueryBuilder()->select('*')->from('tl_example');

        $this->createQuery(new SnowballStemmer())->apply($qb, 'tl_example', 'weihnachten bahn');

        $this->assertSame(
            'SELECT * FROM tl_example WHERE ("tl_example"."searchVector" LIKE :dcValue1) AND ("tl_example"."searchVector" LIKE :dcValue2)',
            $qb->getSQL()
        );
        $this->assertSame(['dcValue1' => '%weihnacht%', 'dcValue2' => '%bahn%'], $qb->getParameters());
    }

    public function testApplyCommaPhrasesAreCombinedWithOr(): void
    {
        $qb = $this->connection->createQueryBuilder()->select('*')->from('tl_example');

        $this->createQuery(new SnowballStemmer())->apply($qb, 'tl_example', 'weihnachten bahn, kind');

        $this->assertSame(
            'SELECT * FROM tl_example WHERE (("tl_example"."searchVector" LIKE :dcValue1) AND ("tl_example"."searchVector" LIKE :dcValue2)) OR ("tl_example"."searchVector" LIKE :dcValue3)',
            $qb->getSQL()
        );
        $this->assertSame(['dcValue1' => '%weihnacht%', 'dcValue2' => '%bahn%', 'dcValue3' => '%kind%'], $qb->getParameters());
    }

    public function testApplyQuotedPhraseIsSingleTerm(): void
    {
        $qb = $this->connection->createQueryBuilder()->select('*')->from('tl_example');

        $this->createQuery(new SnowballStemmer())->apply($qb, 'tl_example', '"bei Nacht"');

        $this->assertSame('SELECT * FROM tl_example WHERE "tl_example"."searchVector" LIKE :dcValue1', $qb->getSQL());
        $this->assertSame(['dcValue1' => '%bei nacht%'], $qb->getParameters());
    }

    public function testApplyUsesDefaultLocaleOfResolver(): void
    {
        $qb = $this->connection->createQueryBuilder()->select('*')->from('tl_example');

        $this->createQuery(new SnowballStemmer())->apply($qb, 'tl_example', 'Schulen');

        $this->assertSame(['dcValue1' => '%schul%'], $qb->getParameters());
    }

    public function testApplyUsesConfiguredLanguage(): void
    {
        $qb = $this->connection->createQueryBuilder()->select('*')->from('tl_example');

        $this->createQuery(new SnowballStemmer(), 'searchVector', static fn (SearchVectorFieldConfiguration $c) => $c->setLanguage('en'))
            ->apply($qb, 'tl_example', 'Schulen');

        $this->assertSame(['dcValue1' => '%schulen%'], $qb->getParameters());
    }

    public function testApplyWithExplicitLocaleDisablesGermanStemming(): void
    {
        $query = $this->createQuery(new SnowballStemmer());

        $german = $this->connection->createQueryBuilder()->select('*')->from('tl_example');
        $query->apply($german, 'tl_example', 'Weihnachten Bahn, "bei Nacht"', null, 'de_CH');

        $english = $this->connection->createQueryBuilder()->select('*')->from('tl_example');
        $query->apply($english, 'tl_example', 'Weihnachten Bahn, "bei Nacht"', null, 'en');

        $this->assertSame(['dcValue1' => '%weihnacht%', 'dcValue2' => '%bahn%', 'dcValue3' => '%bei nacht%'], $german->getParameters());
        $this->assertSame(['dcValue1' => '%weihnachten%', 'dcValue2' => '%bahn%', 'dcValue3' => '%bei nacht%'], $english->getParameters());
    }

    public function testApplyUsesRequestLocaleForTablesWithLanguageField(): void
    {
        $requestStack = new RequestStack();
        $request = new Request();
        $request->setLocale('en');
        $requestStack->push($request);

        $query = $this->createQuery(
            new SnowballStemmer(),
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
        $this->createQuery(new SnowballStemmer(), 'searchVector', null, $requestStack)->apply($qb, 'tl_example', 'Schulen');

        $this->assertSame(['dcValue1' => '%schul%'], $qb->getParameters());
    }

    public function testApplyUsesAliasAndFieldName(): void
    {
        $qb = $this->connection->createQueryBuilder()->select('*')->from('tl_example', 'e');

        $this->createQuery(null, 'myVector')->apply($qb, 'tl_example', 'Bahn', 'e');

        $this->assertSame('SELECT * FROM tl_example e WHERE "e"."myVector" LIKE :dcValue1', $qb->getSQL());
        $this->assertSame(['dcValue1' => '%bahn%'], $qb->getParameters());
    }

    public function testApplyWithoutStemmer(): void
    {
        $qb = $this->connection->createQueryBuilder()->select('*')->from('tl_example');

        $this->createQuery(null)->apply($qb, 'tl_example', 'Weihnachten');

        $this->assertSame(['dcValue1' => '%weihnachten%'], $qb->getParameters());
    }

    public function testApplyKeepsExistingConditions(): void
    {
        $qb = $this->connection->createQueryBuilder()->select('*')->from('tl_example')->where('published = 1');

        $this->createQuery(null)->apply($qb, 'tl_example', 'bahn');

        $this->assertSame(
            'SELECT * FROM tl_example WHERE (published = 1) AND ("tl_example"."searchVector" LIKE :dcValue1)',
            $qb->getSQL()
        );
    }

    public function testApplyEscapesLikeWildcards(): void
    {
        // The tokenizer strips wildcards, so a stemmer has to produce them
        $stemmer = $this->createStemmerStub(['foo' => '50%_off\\x']);
        $qb = $this->connection->createQueryBuilder()->select('*')->from('tl_example');

        $this->assertTrue($this->createQuery($stemmer)->apply($qb, 'tl_example', 'foo'));

        $this->assertSame(['dcValue1' => '%50\\%\\_off\\\\x%'], $qb->getParameters());
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

        $this->assertFalse($this->createQuery(new SnowballStemmer())->apply($qb, 'tl_example', $search));

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
            1 => '| waldbahn bei zwiesel | somm | triebwag |',
            2 => '| weihnachtsmarkt | dresd |',
            3 => '| niederbayern-oberpfalz | liefer nach munch |',
            4 => '',
        ]);
        $query = $this->createQuery(new SnowballStemmer());

        // single word, matched as substring of a compound
        $this->assertSame([1], $this->search($query, 'Waldbahn'));
        $this->assertSame([1], $this->search($query, 'bahn'));
        $this->assertSame([3], $this->search($query, 'Oberpfalz'));
        // inflection and umlaut
        $this->assertSame([3], $this->search($query, 'Lieferung München'));
        // AND
        $this->assertSame([1], $this->search($query, 'sommer triebwagen'));
        $this->assertSame([], $this->search($query, 'sommer weihnachten'));
        // OR
        $this->assertSame([1, 2], $this->search($query, 'sommer, weihnachten'));
        $this->assertSame([1, 2, 3], $this->search($query, 'sommer, dresden, oberpfalz'));
        // phrase
        $this->assertSame([1], $this->search($query, '"bei Zwiesel"'));
        $this->assertSame([], $this->search($query, '"Zwiesel bei"'));
        // word and phrase
        $this->assertSame([1], $this->search($query, 'sommer "bei Zwiesel"'));
        $this->assertSame([], $this->search($query, 'weihnachten "bei Zwiesel"'));
        $this->assertSame([1, 2], $this->search($query, 'dresden, sommer "bei Zwiesel"'));
        // pipe separator keeps a phrase from matching across values
        $this->assertSame([], $this->search($query, '"zwiesel sommer"'));
        // no match
        $this->assertSame([], $this->search($query, 'hamburg'));
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
        [$columns, $values] = $this->createQuery(new SnowballStemmer())->createModelCriteria('tl_example', 'weihnachten bahn, "bei Nacht"');

        $this->assertSame(
            ['(("tl_example"."searchVector" LIKE ? AND "tl_example"."searchVector" LIKE ?) OR ("tl_example"."searchVector" LIKE ?))'],
            $columns
        );
        $this->assertSame(['%weihnacht%', '%bahn%', '%bei nacht%'], $values);
    }

    public function testCreateModelCriteriaWithExplicitLocale(): void
    {
        $query = $this->createQuery(new SnowballStemmer());

        $this->assertSame(['%schul%'], $query->createModelCriteria('tl_example', 'Schulen')[1]);
        $this->assertSame(['%schul%'], $query->createModelCriteria('tl_example', 'Schulen', 'de')[1]);
        $this->assertSame(['%schulen%'], $query->createModelCriteria('tl_example', 'Schulen', 'en')[1]);
    }

    public function testCreateModelCriteriaSingleWord(): void
    {
        [$columns, $values] = $this->createQuery(null)->createModelCriteria('tl_example', 'Bahn');

        $this->assertSame(['(("tl_example"."searchVector" LIKE ?))'], $columns);
        $this->assertSame(['%bahn%'], $values);
    }

    public function testCreateModelCriteriaEscapesLikeWildcards(): void
    {
        $stemmer = $this->createStemmerStub(['foo' => 'a%b_c']);

        [, $values] = $this->createQuery($stemmer)->createModelCriteria('tl_example', 'foo');

        $this->assertSame(['%a\\%b\\_c%'], $values);
    }

    public function testCreateModelCriteriaWithEmptySearch(): void
    {
        $this->assertSame([[], []], $this->createQuery(new SnowballStemmer())->createModelCriteria('tl_example', ' , "" '));
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
}

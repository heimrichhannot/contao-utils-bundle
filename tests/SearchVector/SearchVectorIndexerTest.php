<?php

namespace HeimrichHannot\UtilsBundle\Tests\SearchVector;

use Contao\Controller;
use Contao\CoreBundle\Config\ResourceFinder;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use HeimrichHannot\UtilsBundle\Dca\SearchVectorField;
use HeimrichHannot\UtilsBundle\Event\BuildSearchVectorEvent;
use HeimrichHannot\UtilsBundle\SearchVector\SearchVectorConfigurationResolver;
use HeimrichHannot\UtilsBundle\SearchVector\SearchVectorDataContainer;
use HeimrichHannot\UtilsBundle\SearchVector\SearchVectorIndexer;
use HeimrichHannot\UtilsBundle\SearchVector\SearchVectorLanguageSwitcher;
use HeimrichHannot\UtilsBundle\SearchVector\Normalizer\GermanNormalizer;
use HeimrichHannot\UtilsBundle\SearchVector\Stemmer\SnowballStemmer;
use HeimrichHannot\UtilsBundle\Util\DcaUtil;
use HeimrichHannot\UtilsBundle\Util\FormatterUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Psr\Log\LoggerInterface;
use Contao\CoreBundle\Asset\ContaoContext;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\Translator;

class SearchVectorIndexerTest extends ContaoTestCase
{
    private const TABLE = 'tl_search_vector_test';

    /**
     * The language files of the fixtures are loaded with the real Contao loader.
     */
    private const FIXTURES = __DIR__ . '/../Fixtures/search-vector';

    private const CATEGORY_LABELS = [
        'de' => ['railcar' => 'Triebwagen', 'bus' => 'Linienbusse'],
        'en' => ['railcar' => 'Railcar', 'bus' => 'Buses'],
    ];

    /**
     * Static caches of Contao's language loader, reset per test.
     */
    private const LANGUAGE_CACHES = ['arrLanguageFiles' => [], 'arrLanguages' => [], 'arrAvailableLanguageFiles' => null];

    private Connection $connection;
    private EventDispatcher $eventDispatcher;
    private Translator $translator;
    private array $previousLanguageCaches = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);

        $container = $this->getContainerWithContaoConfiguration();
        $container->set('database_connection', $this->connection);

        // Needed by the Contao 4.13 backend constructor
        $assetsContext = $this->createMock(ContaoContext::class);
        $assetsContext->method('getStaticUrl')->willReturn('');
        $container->set('contao.assets.assets_context', $assetsContext);
        $container->set('contao.assets.files_context', $assetsContext);

        $container->setParameter('kernel.project_dir', self::FIXTURES);
        $container->setParameter('kernel.cache_dir', self::FIXTURES . '/var/cache');
        $container->set('contao.resource_finder', new ResourceFinder([self::FIXTURES . '/contao']));
        System::setContainer($container);

        foreach (self::LANGUAGE_CACHES as $property => $value) {
            $this->previousLanguageCaches[$property] = (new \ReflectionProperty(System::class, $property))->getValue();
            (new \ReflectionProperty(System::class, $property))->setValue(null, $value);
        }

        $this->connection->executeStatement(
            'CREATE TABLE ' . self::TABLE . ' (id INTEGER PRIMARY KEY, language TEXT, title TEXT, text TEXT, category TEXT, seasons TEXT, searchVector TEXT NULL)'
        );

        $this->eventDispatcher = new EventDispatcher();
        $this->translator = new Translator('en');

        // The current user works in English and has loaded the language files of two tables
        $GLOBALS['TL_LANGUAGE'] = 'en';
        System::loadLanguageFile(self::TABLE);
        System::loadLanguageFile('tl_region');

        $GLOBALS['TL_DCA'][self::TABLE] = [
            'config' => [],
            'fields' => [
                'title' => ['inputType' => 'text'],
                'text' => ['inputType' => 'textarea', 'eval' => ['rte' => 'tinyMCE']],
                'category' => [
                    'inputType' => 'select',
                    'options' => ['railcar', 'bus'],
                    'reference' => &$GLOBALS['TL_LANG'][self::TABLE]['category'],
                ],
                'seasons' => [
                    'inputType' => 'checkbox',
                    'options' => ['spring' => 'Frühling', 'winter' => 'Schöne Winterzeit'],
                    'eval' => ['multiple' => true],
                ],
                'region' => [
                    'inputType' => 'select',
                    'options' => ['north', 'south'],
                    'reference' => &$GLOBALS['TL_LANG']['tl_region']['region'],
                ],
            ],
        ];

        SearchVectorField::register(self::TABLE)->setStemmer(SnowballStemmer::class)->setFields(['title', 'text', 'category', 'seasons', 'region', 'notInRow']);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TL_DCA'][self::TABLE], $GLOBALS['TL_LANG'][self::TABLE], $GLOBALS['TL_LANG']['tl_region'], $GLOBALS['TL_LANG']['tl_lazy'], $GLOBALS['TL_LANGUAGE']);

        foreach ($this->previousLanguageCaches as $property => $value) {
            (new \ReflectionProperty(System::class, $property))->setValue(null, $value);
        }

        parent::tearDown();
    }

    public function testBuildVectorResolvesFieldValuesLikeTheBackend(): void
    {
        $vector = $this->createIndexer()->buildVector(self::TABLE, [
            'id' => 1,
            'title' => 'Waldbahn bei Zwiesel',
            'text' => '<p>Fahrt&nbsp;im <strong>Sommer</strong>.</p><p>Mehr:{{link::12}}</p>',
            'category' => 'railcar',
            'seasons' => \serialize(['spring', 'winter']),
        ]);

        // Text and HTML are flattened, insert tags removed, option values replaced by their labels and every array
        // value is a separate section
        $this->assertSame('| waldbahn bei zwiesel | fahrt im somm mehr | triebwag | fruhling | schon winterzeit |', $vector);
    }

    public function testBuildVectorWithoutStemmer(): void
    {
        SearchVectorField::getRegistrations()[self::TABLE]->setStemmer(null);

        $vector = $this->createIndexer()->buildVector(self::TABLE, [
            'id' => 1,
            'title' => 'Größere Brücken',
            'category' => 'bus',
        ]);

        $this->assertSame('| grossere brucken | linienbusse |', $vector);
    }

    public function testBuildVectorRemovesDuplicatesAndReturnsEmptyStringWithoutContent(): void
    {
        $indexer = $this->createIndexer();

        $this->assertSame('| triebwag |', $indexer->buildVector(self::TABLE, ['id' => 1, 'title' => 'Triebwagen', 'category' => 'railcar']));
        $this->assertSame('', $indexer->buildVector(self::TABLE, ['id' => 1, 'title' => '<p> </p>', 'text' => '']));
    }

    public function testNormalizerUnifiesSpellings(): void
    {
        $indexer = $this->createIndexer();

        // Without normalizer, a transliterated umlaut leads to another stem
        $this->assertSame('| muench | munch |', $indexer->buildVector(self::TABLE, ['id' => 1, 'title' => 'Muenchen', 'text' => 'München']));

        SearchVectorField::getRegistrations()[self::TABLE]->setNormalizer(GermanNormalizer::class);
        $this->assertSame('| munch |', $indexer->buildVector(self::TABLE, ['id' => 1, 'title' => 'Muenchen', 'text' => 'München']));
        $this->assertSame('| feu |', $indexer->buildVector(self::TABLE, ['id' => 1, 'title' => 'Feuer']));
    }

    public function testEventCanAddValues(): void
    {
        $this->eventDispatcher->addListener(BuildSearchVectorEvent::class, function (BuildSearchVectorEvent $event): void {
            $this->assertSame(self::TABLE, $event->table);
            $this->assertSame(1, $event->row['id']);
            $this->assertSame(['Waldbahn'], $event->getValues());
            $this->assertSame('de', $event->locale);

            $event->addValue('Bayerischer Wald');
        });

        $this->assertSame(
            '| waldbahn | bayer wald |',
            $this->createIndexer()->buildVector(self::TABLE, ['id' => 1, 'title' => 'Waldbahn'])
        );
    }

    public function testLabelsAreResolvedAndStemmedInTheRecordLanguage(): void
    {
        SearchVectorField::getRegistrations()[self::TABLE]->setLanguageField('language');
        $indexer = $this->createIndexer();

        $this->assertSame('| triebwag |', $indexer->buildVector(self::TABLE, ['id' => 1, 'language' => 'de', 'category' => 'railcar']));

        // The language of the user is restored
        $this->assertSame('en', $GLOBALS['TL_LANGUAGE']);
        $this->assertSame(self::CATEGORY_LABELS['en'], $GLOBALS['TL_LANG'][self::TABLE]['category']);
        $this->assertSame('en', $this->translator->getLocale());

        // English records are stemmed with the English rules
        $this->assertSame('| train | railcar |', $indexer->buildVector(self::TABLE, ['id' => 2, 'language' => 'en', 'title' => 'Trains', 'category' => 'railcar']));

        // An empty language field falls back to the configured language, then to the default locale
        $this->assertSame('| triebwag |', $indexer->buildVector(self::TABLE, ['id' => 3, 'language' => '', 'category' => 'railcar']));
        SearchVectorField::getRegistrations()[self::TABLE]->setLanguage('en');
        $this->assertSame('| railcar |', $indexer->buildVector(self::TABLE, ['id' => 3, 'language' => '', 'category' => 'railcar']));
    }

    public function testOptionsCallbacksRunInTheRecordLanguage(): void
    {
        $GLOBALS['TL_DCA'][self::TABLE]['fields']['seasons']['options_callback'] = ['Foo', 'bar'];

        $dcaUtil = $this->createMock(DcaUtil::class);
        $dcaUtil->method('executeCallback')->willReturnCallback(fn (): array => [
            'winter' => 'de' === $this->translator->getLocale() ? 'Schnee' : 'Snow',
        ]);

        $vector = $this->createIndexer($dcaUtil)->buildVector(self::TABLE, ['id' => 1, 'seasons' => \serialize(['winter'])]);

        $this->assertSame('| schnee |', $vector);
        $this->assertSame('en', $this->translator->getLocale());
    }

    public function testTableTranslationsAreLoadedWhenTheLanguageIsUnchanged(): void
    {
        // A German user in the frontend or on the command line, the table translations are not loaded yet
        $GLOBALS['TL_LANGUAGE'] = 'de';
        $this->translator->setLocale('de');
        $GLOBALS['TL_LANG'][self::TABLE]['category'] = null;
        (new \ReflectionProperty(System::class, 'arrLanguageFiles'))->setValue(null, []);

        $this->assertSame('| triebwag |', $this->createIndexer()->buildVector(self::TABLE, ['id' => 1, 'category' => 'railcar']));
    }

    public function testReferencesToLanguageFilesOfOtherTablesUseTheRecordLanguage(): void
    {
        $vector = $this->createIndexer()->buildVector(self::TABLE, ['id' => 1, 'region' => 'south']);

        $this->assertSame('| sud |', $vector);
        $this->assertSame('South', $GLOBALS['TL_LANG']['tl_region']['region']['south'], 'The language of the user is restored');
    }

    public function testLanguageFilesLoadedWhileIndexingAreRestored(): void
    {
        $GLOBALS['TL_DCA'][self::TABLE]['fields']['seasons']['options_callback'] = ['Foo', 'bar'];

        $dcaUtil = $this->createMock(DcaUtil::class);
        $dcaUtil->method('executeCallback')->willReturnCallback(static function (): array {
            System::loadLanguageFile('tl_lazy');

            return ['winter' => $GLOBALS['TL_LANG']['tl_lazy']['winter']];
        });

        $vector = $this->createIndexer($dcaUtil)->buildVector(self::TABLE, ['id' => 1, 'seasons' => \serialize(['winter'])]);

        $this->assertSame('| schnee |', $vector);
        $this->assertSame('Snow', $GLOBALS['TL_LANG']['tl_lazy']['winter'], 'The language of the user is restored');
    }

    public function testUnresolvableFieldFallsBackToRawValue(): void
    {
        $GLOBALS['TL_DCA'][self::TABLE]['fields']['category']['options_callback'] = ['Foo', 'bar'];

        $dcaUtil = $this->createMock(DcaUtil::class);
        $dcaUtil->method('executeCallback')->willThrowException(new \RuntimeException('No user'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')->with($this->stringContains('category'));

        $vector = $this->createIndexer($dcaUtil, $logger)->buildVector(self::TABLE, ['id' => 1, 'category' => 'railcar']);

        $this->assertSame('| railcar |', $vector);
    }

    public function testIndexWritesOnlyChangedVectors(): void
    {
        $this->connection->insert(self::TABLE, ['id' => 1, 'title' => 'Waldbahn', 'category' => 'bus']);
        $this->connection->insert(self::TABLE, ['id' => 2, 'title' => 'Triebwagen']);

        $indexer = $this->createIndexer();

        $this->assertTrue($indexer->index(self::TABLE, 1));
        $this->assertSame('| waldbahn | linienbuss |', $this->fetchVector(1));
        $this->assertNull($this->fetchVector(2));

        $this->assertFalse($indexer->index(self::TABLE, 1), 'An unchanged vector is not written again');
        $this->assertFalse($indexer->index(self::TABLE, 99), 'A missing record is ignored');

        $this->connection->update(self::TABLE, ['title' => 'Oberpfalz'], ['id' => 1]);
        $this->assertTrue($indexer->index(self::TABLE, 1));
        $this->assertSame('| oberpfalz | linienbuss |', $this->fetchVector(1));
    }

    public function testIndexAll(): void
    {
        $this->connection->insert(self::TABLE, ['id' => 1, 'title' => 'Waldbahn']);
        $this->connection->insert(self::TABLE, ['id' => 2, 'title' => 'Triebwagen']);

        $indexer = $this->createIndexer();
        $indexer->index(self::TABLE, 1);

        $progress = [];
        $changed = $indexer->indexAll(self::TABLE, static function (int $id, bool $changed) use (&$progress): void {
            $progress[$id] = $changed;
        });

        $this->assertSame(1, $changed);
        $this->assertSame([1 => false, 2 => true], $progress);
        $this->assertSame('| triebwag |', $this->fetchVector(2));
    }

    public function testUnregisteredTable(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->createIndexer()->index('tl_unknown', 1);
    }

    public function testDataContainerProvidesRecord(): void
    {
        $row = ['id' => '5', 'title' => 'Waldbahn'];
        $dc = new SearchVectorDataContainer(self::TABLE, $row);

        $this->assertSame(self::TABLE, $dc->table);
        $this->assertSame(5, $dc->id);
        $this->assertSame('Waldbahn', $dc->activeRecord->title);
        $this->assertSame($row, $dc->getCurrentRecord());
        $this->assertSame($row, $dc->getCurrentRecord(5, self::TABLE));
    }

    private function createIndexer(?DcaUtil $dcaUtil = null, ?LoggerInterface $logger = null): SearchVectorIndexer
    {
        // There are no DCA files, the DCA is set up in setUp()
        $framework = $this->mockContaoFramework([Controller::class => $this->mockAdapter(['loadDataContainer'])]);

        // Language files are loaded for real
        $languageFramework = $this->mockContaoFramework([Controller::class => new Adapter(Controller::class)]);

        $utils = $this->createMock(Utils::class);
        $utils->method('dca')->willReturn($dcaUtil ?? $this->createMock(DcaUtil::class));

        $insertTagParser = $this->createMock(InsertTagParser::class);
        $insertTagParser->expects($this->never())->method('replace');

        return new SearchVectorIndexer(
            $this->connection,
            new SearchVectorConfigurationResolver(
                $framework,
                new ServiceLocator([SnowballStemmer::class => static fn () => new SnowballStemmer()]),
                new ServiceLocator([GermanNormalizer::class => static fn () => new GermanNormalizer()]),
                new RequestStack(),
                'de'
            ),
            new FormatterUtil($languageFramework, $insertTagParser, $utils, []),
            $this->eventDispatcher,
            $logger ?? new NullLogger(),
            new SearchVectorLanguageSwitcher($languageFramework, $this->translator),
        );
    }

    private function fetchVector(int $id): ?string
    {
        $vector = $this->connection->fetchOne('SELECT searchVector FROM ' . self::TABLE . ' WHERE id = ?', [$id]);

        return false === $vector ? null : $vector;
    }
}

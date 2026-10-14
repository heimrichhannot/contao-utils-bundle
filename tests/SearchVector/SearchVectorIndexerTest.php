<?php

namespace HeimrichHannot\UtilsBundle\Tests\SearchVector;

use Contao\Controller;
use Contao\CoreBundle\Config\ResourceFinder;
use Contao\CoreBundle\DataContainer\VirtualFieldsHandler;
use Contao\CoreBundle\Framework\Adapter;
use Contao\DcaExtractor;
use Contao\CoreBundle\Util\LocaleUtil;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\DefaultSchemaManagerFactory;
use HeimrichHannot\UtilsBundle\Dca\SearchVectorField;
use HeimrichHannot\UtilsBundle\Event\BuildSearchVectorEvent;
use HeimrichHannot\UtilsBundle\SearchVector\SearchVectorConfigurationResolver;
use HeimrichHannot\UtilsBundle\SearchVector\SearchVectorDataContainer;
use HeimrichHannot\UtilsBundle\SearchVector\Messenger\RebuildSearchVectorMessage;
use HeimrichHannot\UtilsBundle\SearchVector\Messenger\RebuildSearchVectorMessageHandler;
use HeimrichHannot\UtilsBundle\SearchVector\SearchVectorIndexer;
use HeimrichHannot\UtilsBundle\SearchVector\SearchVectorLanguageSwitcher;
use HeimrichHannot\UtilsBundle\SearchVector\Normalizer\GermanNormalizer;
use HeimrichHannot\UtilsBundle\Tests\SearchVector\Stemmer\SuffixTestStemmer;
use HeimrichHannot\UtilsBundle\SearchVector\Stemmer\SnowballStemmer;
use HeimrichHannot\UtilsBundle\SearchVector\Tokenizer;
use HeimrichHannot\UtilsBundle\Util\DcaUtil;
use HeimrichHannot\UtilsBundle\Util\FormatterUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
use Symfony\Component\Messenger\Transport\Sender\SendersLocator;
use Symfony\Component\Translation\LocaleSwitcher;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\LocaleAwareInterface;

class SearchVectorIndexerTest extends ContaoTestCase
{
    private const TABLE = 'tl_search_vector_test';

    /**
     * The language files of the fixtures are loaded with the real Contao loader.
     */
    private const FIXTURES = __DIR__ . '/../Fixtures/search-vector';

    private const CATEGORY_LABELS = [
        'de' => ['guided_tour' => 'Stadtführung', 'museum' => 'Museen'],
        'en' => ['guided_tour' => 'City tour', 'museum' => 'Museums'],
    ];

    /**
     * Static caches of Contao's language loader, reset per test.
     */
    private const LANGUAGE_CACHES = ['arrLanguageFiles' => [], 'arrLanguages' => [], 'arrAvailableLanguageFiles' => null];

    private Connection $connection;
    private EventDispatcher $eventDispatcher;
    private Translator $translator;
    private LocaleSwitcher $localeSwitcher;
    private array $previousLanguageCaches = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true], $this->createDbalConfiguration());

        $container = $this->getContainerWithContaoConfiguration();
        $container->set('database_connection', $this->connection);

        $container->setParameter('kernel.project_dir', self::FIXTURES);
        $container->setParameter('kernel.cache_dir', self::FIXTURES . '/var/cache');
        $container->set('contao.resource_finder', new ResourceFinder([self::FIXTURES . '/contao']));
        System::setContainer($container);

        foreach (self::LANGUAGE_CACHES as $property => $value) {
            $this->previousLanguageCaches[$property] = (new \ReflectionProperty(System::class, $property))->getValue();
            (new \ReflectionProperty(System::class, $property))->setValue(null, $value);
        }

        $this->connection->executeStatement(
            'CREATE TABLE ' . self::TABLE . ' (id INTEGER PRIMARY KEY, language TEXT, title TEXT, text TEXT, category TEXT, seasons TEXT, jsonData TEXT NULL, searchVector TEXT NULL)'
        );

        $this->eventDispatcher = new EventDispatcher();
        $this->translator = new Translator('en');
        // Since Contao 5.6.3, $GLOBALS['TL_LANGUAGE'] follows the locale switcher. Stands in for Contao's internal
        // legacy locale switcher, which is deprecated and scheduled for removal.
        $legacyLanguage = new class() implements LocaleAwareInterface {
            public function setLocale(string $locale): void
            {
                $GLOBALS['TL_LANGUAGE'] = LocaleUtil::formatAsLanguageTag($locale);
            }

            public function getLocale(): string
            {
                return LocaleUtil::formatAsLocale($GLOBALS['TL_LANGUAGE'] ?? 'en');
            }
        };

        $this->localeSwitcher = new LocaleSwitcher('en', [$this->translator, $legacyLanguage]);

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
                    'options' => ['guided_tour', 'museum'],
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

        SearchVectorField::register(self::TABLE)->setStemmer(SuffixTestStemmer::class)->setFields(['title', 'text', 'category', 'seasons', 'region', 'notInRow']);
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
            'title' => 'Stadtmuseum am Rathaus',
            'text' => '<p>Führung&nbsp;im <strong>Sommer</strong>.</p><p>Mehr:{{link::12}}</p>',
            'category' => 'guided_tour',
            'seasons' => \serialize(['spring', 'winter']),
        ]);

        // Text and HTML are flattened, insert tags removed, option values replaced by their labels and every array
        // value is a separate section
        $this->assertSame('| stadtmuseum am rathaus | fuhr im somm mehr | fuhrung im sommer mehr | stadtfuhr | stadtfuhrung | fruhling | schon winterzeit | schone winterzeit |', $vector);
    }

    public function testBuildVectorWithoutStemmer(): void
    {
        SearchVectorField::getRegistrations()[self::TABLE]->setStemmer(null);

        $vector = $this->createIndexer()->buildVector(self::TABLE, [
            'id' => 1,
            'title' => 'Größere Brücken',
            'category' => 'museum',
        ]);

        $this->assertSame('| grossere brucken | museen |', $vector);
    }

    public function testBuildVectorRemovesDuplicatesAndReturnsEmptyStringWithoutContent(): void
    {
        $indexer = $this->createIndexer();

        $this->assertSame('| stadtfuhr | stadtfuhrung |', $indexer->buildVector(self::TABLE, ['id' => 1, 'title' => 'Stadtführung', 'category' => 'guided_tour']));
        $this->assertSame('', $indexer->buildVector(self::TABLE, ['id' => 1, 'title' => '<p> </p>', 'text' => '']));
    }

    public function testCompoundsAreFoundByTheirLastWord(): void
    {
        SearchVectorField::getRegistrations()[self::TABLE]->setStemmer(SnowballStemmer::class);

        $vector = $this->createIndexer()->buildVector(self::TABLE, ['id' => 1, 'title' => 'Imkerhonig vom Bauernhof']);

        // The stem of the compound differs from the stem of its last word ("imkerhon", but "honig")
        $this->assertSame('| imkerhon vom bauernhof | imkerhonig vom bauernhof |', $vector);

        foreach (['Honig', 'Imkerhonig', '"Imkerhonig vom Bauernhof"'] as $search) {
            foreach (Tokenizer::parseQuery($search, 'de', new SnowballStemmer()) as $terms) {
                foreach ($terms as $term) {
                    $this->assertStringContainsString($term, $vector, $search);
                }
            }
        }
    }

    public function testNormalizerUnifiesSpellings(): void
    {
        $indexer = $this->createIndexer();

        // Without normalizer, a transliterated umlaut leads to another stem
        $this->assertSame('| muench | muenchen | munch | munchen |', $indexer->buildVector(self::TABLE, ['id' => 1, 'title' => 'Muenchen', 'text' => 'München']));

        SearchVectorField::getRegistrations()[self::TABLE]->setNormalizer(GermanNormalizer::class);
        $this->assertSame('| munch | munchen |', $indexer->buildVector(self::TABLE, ['id' => 1, 'title' => 'Muenchen', 'text' => 'München']));
        $this->assertSame('| feu | feuer |', $indexer->buildVector(self::TABLE, ['id' => 1, 'title' => 'Feuer']));
    }

    public function testEventCanAddValues(): void
    {
        $this->eventDispatcher->addListener(BuildSearchVectorEvent::class, function (BuildSearchVectorEvent $event): void {
            $this->assertSame(self::TABLE, $event->table);
            $this->assertSame(1, $event->row['id']);
            $this->assertSame(['Stadtmuseum'], $event->getValues());
            $this->assertSame('de', $event->locale);

            $event->addValue('Historische Altstadt');
        });

        $this->assertSame(
            '| stadtmuseum | historisch altstadt | historische altstadt |',
            $this->createIndexer()->buildVector(self::TABLE, ['id' => 1, 'title' => 'Stadtmuseum'])
        );
    }

    public function testLabelsAreResolvedAndStemmedInTheRecordLanguage(): void
    {
        SearchVectorField::getRegistrations()[self::TABLE]->setLanguageField('language');
        $indexer = $this->createIndexer();

        $this->assertSame('| stadtfuhr | stadtfuhrung |', $indexer->buildVector(self::TABLE, ['id' => 1, 'language' => 'de', 'category' => 'guided_tour']));

        // The language of the user is restored
        $this->assertSame('en', $GLOBALS['TL_LANGUAGE']);
        $this->assertSame(self::CATEGORY_LABELS['en'], $GLOBALS['TL_LANG'][self::TABLE]['category']);
        $this->assertSame('en', $this->translator->getLocale());

        // English records are not stemmed with the German rules ("Guide" would lose its "e")
        $this->assertSame('| guide | city tour |', $indexer->buildVector(self::TABLE, ['id' => 2, 'language' => 'en', 'title' => 'Guide', 'category' => 'guided_tour']));

        // An empty language field falls back to the configured language, then to the default locale
        $this->assertSame('| stadtfuhr | stadtfuhrung |', $indexer->buildVector(self::TABLE, ['id' => 3, 'language' => '', 'category' => 'guided_tour']));
        SearchVectorField::getRegistrations()[self::TABLE]->setLanguage('en');
        $this->assertSame('| city tour |', $indexer->buildVector(self::TABLE, ['id' => 3, 'language' => '', 'category' => 'guided_tour']));
    }

    public function testOptionsCallbacksRunInTheRecordLanguage(): void
    {
        $GLOBALS['TL_DCA'][self::TABLE]['fields']['seasons']['options_callback'] = ['Foo', 'bar'];

        $dcaUtil = $this->createMock(DcaUtil::class);
        $dcaUtil->method('executeCallback')->willReturnCallback(fn (): array => [
            'winter' => 'de' === $this->translator->getLocale() ? 'Schnee' : 'Snow',
        ]);

        $vector = $this->createIndexer($dcaUtil)->buildVector(self::TABLE, ['id' => 1, 'seasons' => \serialize(['winter'])]);

        $this->assertSame('| schne | schnee |', $vector);
        $this->assertSame('en', $this->translator->getLocale());
    }

    public function testTableTranslationsAreLoadedWhenTheLanguageIsUnchanged(): void
    {
        // A German user in the frontend or on the command line, the table translations are not loaded yet
        // Set the way Contao 5.3 (translator, $GLOBALS['TL_LANGUAGE']) and Contao 5.6.3+ (locale switcher) do it
        $GLOBALS['TL_LANGUAGE'] = 'de';
        $this->localeSwitcher->setLocale('de');
        $GLOBALS['TL_LANG'][self::TABLE]['category'] = null;
        (new \ReflectionProperty(System::class, 'arrLanguageFiles'))->setValue(null, []);

        $this->assertSame('| stadtfuhr | stadtfuhrung |', $this->createIndexer()->buildVector(self::TABLE, ['id' => 1, 'category' => 'guided_tour']));
    }

    public function testReferencesToLanguageFilesOfOtherTablesUseTheRecordLanguage(): void
    {
        $vector = $this->createIndexer()->buildVector(self::TABLE, ['id' => 1, 'region' => 'south']);

        $this->assertSame('| sud | suden |', $vector);
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

        $this->assertSame('| schne | schnee |', $vector);
        $this->assertSame('Snow', $GLOBALS['TL_LANG']['tl_lazy']['winter'], 'The language of the user is restored');
    }

    public function testUnresolvableFieldFallsBackToRawValue(): void
    {
        $GLOBALS['TL_DCA'][self::TABLE]['fields']['category']['options_callback'] = ['Foo', 'bar'];

        $dcaUtil = $this->createMock(DcaUtil::class);
        $dcaUtil->method('executeCallback')->willThrowException(new \RuntimeException('No user'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')->with($this->stringContains('category'));

        $vector = $this->createIndexer($dcaUtil, $logger)->buildVector(self::TABLE, ['id' => 1, 'category' => 'guided_tour']);

        $this->assertSame('| guided tour |', $vector);
    }

    public function testIndexWritesOnlyChangedVectors(): void
    {
        $this->connection->insert(self::TABLE, ['id' => 1, 'title' => 'Stadtmuseum', 'category' => 'museum']);
        $this->connection->insert(self::TABLE, ['id' => 2, 'title' => 'Stadtführung']);

        $indexer = $this->createIndexer();

        $this->assertTrue($indexer->index(self::TABLE, 1));
        $this->assertSame('| stadtmuseum | muse | museen |', $this->fetchVector(1));
        $this->assertNull($this->fetchVector(2));

        $this->assertFalse($indexer->index(self::TABLE, 1), 'An unchanged vector is not written again');
        $this->assertFalse($indexer->index(self::TABLE, 99), 'A missing record is ignored');

        $this->connection->update(self::TABLE, ['title' => 'Rathaus'], ['id' => 1]);
        $this->assertTrue($indexer->index(self::TABLE, 1));
        $this->assertSame('| rathaus | muse | museen |', $this->fetchVector(1));
    }

    public function testQueueSendsMessageThatAWorkerHandles(): void
    {
        $this->connection->insert(self::TABLE, ['id' => 1, 'title' => 'Stadtmuseum']);

        // A messenger setup like Contao's, with the transport of the Contao Managed Edition
        $transport = new InMemoryTransport();
        $transports = new ServiceLocator([SearchVectorIndexer::TRANSPORT => static fn () => $transport]);
        $handlers = [];
        $bus = new MessageBus([
            new SendMessageMiddleware(new SendersLocator([], $transports)),
            new HandleMessageMiddleware(new HandlersLocator([RebuildSearchVectorMessage::class => [static function (RebuildSearchVectorMessage $message) use (&$handlers): void {
                $handlers[0]($message);
            }]])),
        ]);
        $indexer = $this->createIndexer(messageBus: $bus, messengerTransports: $transports);
        $handlers[0] = new RebuildSearchVectorMessageHandler($indexer);

        $indexer->queue(self::TABLE, 1);

        $this->assertNull($this->fetchVector(1), 'The vector is not built before a worker handles the message');
        $this->assertCount(1, $sent = $transport->getSent());
        $this->assertEquals(new RebuildSearchVectorMessage(self::TABLE, 1), $sent[0]->getMessage());
        $this->assertSame([SearchVectorIndexer::TRANSPORT], $sent[0]->last(TransportNamesStamp::class)->getTransportNames());

        // The worker receives the message from the transport
        $bus->dispatch($sent[0]->with(new ReceivedStamp(SearchVectorIndexer::TRANSPORT)));

        $this->assertSame('| stadtmuseum |', $this->fetchVector(1));
    }

    public function testQueueIndexesRightAwayWithoutTransport(): void
    {
        $this->connection->insert(self::TABLE, ['id' => 1, 'title' => 'Stadtmuseum']);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->never())->method('dispatch');

        $this->createIndexer(messageBus: $bus, messengerTransports: new ServiceLocator([]))->queue(self::TABLE, 1);
        $this->assertSame('| stadtmuseum |', $this->fetchVector(1));

        $this->connection->update(self::TABLE, ['title' => 'Rathaus'], ['id' => 1]);
        $this->createIndexer()->queue(self::TABLE, 1);
        $this->assertSame('| rathaus |', $this->fetchVector(1), 'Without message bus');
    }

    public function testQueueFailsEarlyForUnregisteredTable(): void
    {
        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->never())->method('dispatch');

        $this->expectException(\InvalidArgumentException::class);

        $this->createIndexer(messageBus: $bus, messengerTransports: new ServiceLocator([SearchVectorIndexer::TRANSPORT => static fn () => new InMemoryTransport()]))
            ->queue('tl_unknown', 1);
    }

    public function testIndexRebuildsTheVectorInATransaction(): void
    {
        $this->connection->insert(self::TABLE, ['id' => 1, 'title' => 'Museum']);

        $inTransaction = null;
        $this->eventDispatcher->addListener(BuildSearchVectorEvent::class, function () use (&$inTransaction): void {
            $inTransaction = $this->connection->isTransactionActive();
        });

        $this->assertTrue($this->createIndexer()->index(self::TABLE, 1));
        $this->assertTrue($inTransaction, 'The record is read, indexed and written in one transaction');
        $this->assertFalse($this->connection->isTransactionActive());
    }

    public function testVirtualFieldsAreExpanded(): void
    {
        if (!\class_exists(VirtualFieldsHandler::class)) {
            $this->markTestSkipped('Virtual fields exist since Contao 5.7.');
        }

        SearchVectorField::getRegistrations()[self::TABLE]->setFields(['title', 'subtitle']);
        $this->connection->insert(self::TABLE, ['id' => 1, 'title' => 'Museum', 'jsonData' => '{"subtitle":"Führung im Sommer"}']);

        $dcaExtractor = $this->createMock(DcaExtractor::class);
        $dcaExtractor->method('getVirtualTargets')->willReturn(['jsonData']);
        $dcaExtractor->method('getVirtualFields')->willReturn(['subtitle' => 'jsonData']);

        // Contao's own handler, as used by models and DC_Table
        $handler = new VirtualFieldsHandler($this->mockContaoFramework([], [DcaExtractor::class => $dcaExtractor]));

        $this->assertTrue($this->createIndexer(virtualFieldsHandler: $handler)->index(self::TABLE, 1));
        $this->assertSame('| museum | fuhr im somm |', $this->fetchVector(1));
    }

    public function testIndexAll(): void
    {
        $this->connection->insert(self::TABLE, ['id' => 1, 'title' => 'Stadtmuseum']);
        $this->connection->insert(self::TABLE, ['id' => 2, 'title' => 'Stadtführung']);

        $indexer = $this->createIndexer();
        $indexer->index(self::TABLE, 1);

        $progress = [];
        $changed = $indexer->indexAll(self::TABLE, static function (int $id, bool $changed) use (&$progress): void {
            $progress[$id] = $changed;
        });

        $this->assertSame(1, $changed);
        $this->assertSame([1 => false, 2 => true], $progress);
        $this->assertSame('| stadtfuhr | stadtfuhrung |', $this->fetchVector(2));
    }

    public function testUnregisteredTable(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->createIndexer()->index('tl_unknown', 1);
    }

    public function testDataContainerProvidesRecord(): void
    {
        $row = ['id' => '5', 'title' => 'Stadtmuseum'];
        $dc = new SearchVectorDataContainer(self::TABLE, $row);

        $this->assertSame(self::TABLE, $dc->table);
        $this->assertSame(5, $dc->id);
        // Reading $dc->activeRecord is deprecated, check what callbacks that still use it would get
        $this->assertSame('Stadtmuseum', (new \ReflectionProperty(SearchVectorDataContainer::class, 'objActiveRecord'))->getValue($dc)->title);
        $this->assertSame($row, $dc->getCurrentRecord());
        $this->assertSame($row, $dc->getCurrentRecord(5, self::TABLE));
    }

    private function createIndexer(
        ?DcaUtil $dcaUtil = null,
        ?LoggerInterface $logger = null,
        ?MessageBusInterface $messageBus = null,
        ?ServiceLocator $messengerTransports = null,
        ?object $virtualFieldsHandler = null,
    ): SearchVectorIndexer {
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
                new ServiceLocator([
                    SuffixTestStemmer::class => static fn () => new SuffixTestStemmer(),
                    SnowballStemmer::class => static fn () => new SnowballStemmer(),
                ]),
                new ServiceLocator([GermanNormalizer::class => static fn () => new GermanNormalizer()]),
                new RequestStack(),
                'de'
            ),
            new FormatterUtil($languageFramework, $insertTagParser, $utils, []),
            $this->eventDispatcher,
            $logger ?? new NullLogger(),
            new SearchVectorLanguageSwitcher($languageFramework, $this->translator, $this->localeSwitcher),
            $messageBus,
            $messengerTransports,
            $virtualFieldsHandler,
        );
    }

    private function fetchVector(int $id): ?string
    {
        $vector = $this->connection->fetchOne('SELECT searchVector FROM ' . self::TABLE . ' WHERE id = ?', [$id]);

        return false === $vector ? null : $vector;
    }

    private function createDbalConfiguration(): Configuration
    {
        // Leaving the schema manager factory unset is deprecated
        $configuration = new Configuration();
        $configuration->setSchemaManagerFactory(new DefaultSchemaManagerFactory());

        return $configuration;
    }
}

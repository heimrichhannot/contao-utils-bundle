<?php

namespace HeimrichHannot\UtilsBundle\SearchVector;

use Contao\DataContainer;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use HeimrichHannot\UtilsBundle\Event\BuildSearchVectorEvent;
use HeimrichHannot\UtilsBundle\SearchVector\Messenger\RebuildSearchVectorMessage;
use HeimrichHannot\UtilsBundle\Util\FormatterUtil;
use HeimrichHannot\UtilsBundle\Util\FormatterUtil\FormatDcaFieldValueOptions;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Builds and stores the search vector of records whose table registered a SearchVectorField.
 *
 * Format: the tokens of one value are joined by a space, values are separated by ` | ` and the whole string is
 * wrapped in `| … |`, e.g. `| fuhrung durch die altstadt | sommer | stadtrundgange |` (without stemmer). The space
 * keeps word adjacency for quoted phrase searches, the pipe stops a phrase from matching across two unrelated values.
 *
 * Option labels are resolved and words are stemmed in the language of the record (see
 * SearchVectorFieldConfiguration::setLanguage() and setLanguageField()), never in the language of the current user.
 */
class SearchVectorIndexer
{
    /**
     * The messenger transport of asynchronous rebuilds, configured by the Contao Managed Edition.
     */
    public const TRANSPORT = 'contao_prio_normal';

    private const VALUE_SEPARATOR = ' | ';

    /**
     * Joins array values in the formatter output, so each array value stays a separate value of the vector.
     */
    private const ARRAY_JOINER = "\x1F";

    public function __construct(
        private readonly Connection $connection,
        private readonly SearchVectorConfigurationResolver $configurationResolver,
        private readonly FormatterUtil $formatterUtil,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger,
        private readonly SearchVectorLanguageSwitcher $languageSwitcher,
        private readonly ?MessageBusInterface $messageBus = null,
        private readonly ?ContainerInterface $messengerTransports = null,
    ) {}

    /**
     * Rebuilds the search vector of a single record asynchronously.
     *
     * Sends a message to the contao_prio_normal transport, which a messenger worker or, without a running worker,
     * Contao's web worker at the end of the request processes. Rebuilds the vector right away if the transport is not
     * available.
     *
     * @throws \InvalidArgumentException if the table has no search vector field
     */
    public function queue(string $table, int $id): void
    {
        // Fail early instead of in the worker
        $this->configurationResolver->getConfiguration($table);

        if ($id < 1) {
            return;
        }

        if (!$this->messageBus || !$this->messengerTransports?->has(self::TRANSPORT)) {
            $this->index($table, $id);

            return;
        }

        // A stamp instead of routing configuration, as the Contao routing interfaces are deprecated since Contao 5.6
        // and the #[AsMessage] attribute needs Symfony 7.2
        $this->messageBus->dispatch(new RebuildSearchVectorMessage($table, $id), [new TransportNamesStamp([self::TRANSPORT])]);
    }

    /**
     * Rebuilds the search vector of a single record.
     *
     * The record is read from the database, so this can be called from any save path after the record was written.
     *
     * @param DataContainer|null $dc The data container passed to options callbacks. If omitted, a minimal one
     *                               holding table, id and record is used.
     *
     * @return bool Whether the stored value changed
     */
    public function index(string $table, int $id, ?DataContainer $dc = null): bool
    {
        $column = $this->configurationResolver->getConfiguration($table)->getFieldName();

        if ($id < 1 || !$row = $this->connection->fetchAssociative(
            \sprintf('SELECT * FROM %s WHERE id = ?', $this->connection->quoteIdentifier($table)),
            [$id]
        )) {
            return false;
        }

        $vector = $this->buildVector($table, $row, $dc);

        if (($row[$column] ?? null) === $vector) {
            return false;
        }

        $this->connection->update(
            $this->connection->quoteIdentifier($table),
            [$this->connection->quoteIdentifier($column) => $vector],
            ['id' => $id]
        );

        return true;
    }

    /**
     * Rebuilds the search vector of every record of a table.
     *
     * @param (callable(int $id, bool $changed): void)|null $onProgress
     *
     * @return int The number of records whose vector changed
     */
    public function indexAll(string $table, ?callable $onProgress = null): int
    {
        $this->configurationResolver->getConfiguration($table);

        $ids = $this->connection->fetchFirstColumn(
            \sprintf('SELECT id FROM %s ORDER BY id', $this->connection->quoteIdentifier($table))
        );

        $changed = 0;

        foreach ($ids as $id) {
            $didChange = $this->index($table, (int) $id);

            if ($didChange) {
                ++$changed;
            }

            if ($onProgress) {
                $onProgress((int) $id, $didChange);
            }
        }

        return $changed;
    }

    /**
     * Builds the search vector from a raw database row.
     *
     * @param array<string, mixed> $row
     */
    public function buildVector(string $table, array $row, ?DataContainer $dc = null): string
    {
        $configuration = $this->configurationResolver->getConfiguration($table);
        $stemmer = $this->configurationResolver->getStemmer($configuration);
        $normalizer = $this->configurationResolver->getNormalizer($configuration);
        $locale = $this->configurationResolver->getRecordLocale($configuration, $row);
        $dc ??= new SearchVectorDataContainer($table, $row);

        $values = $this->languageSwitcher->run($locale, $table, fn (): array => $this->formatValues($table, $row, $configuration->getFields(), $dc));

        $event = $this->eventDispatcher->dispatch(new BuildSearchVectorEvent($table, $row, $values, $locale));

        $parts = [];

        foreach ($event->getValues() as $value) {
            if ($tokens = Tokenizer::tokenize($value, $locale, $stemmer, $normalizer)) {
                $parts[] = \implode(' ', $tokens);
            }
        }

        if (!$parts = \array_values(\array_unique($parts))) {
            return '';
        }

        return '| ' . \implode(self::VALUE_SEPARATOR, $parts) . ' |';
    }

    /**
     * Resolves the field values like the backend does: option labels, foreign keys, plain text instead of HTML.
     *
     * @param array<string, mixed> $row
     * @param list<string>         $fields
     *
     * @return list<string>
     */
    private function formatValues(string $table, array $row, array $fields, DataContainer $dc): array
    {
        $values = [];

        foreach ($fields as $field) {
            $raw = $row[$field] ?? null;

            if (null === $raw || '' === $raw) {
                continue;
            }

            $raw = \is_scalar($raw) ? (string) $raw : $raw;

            try {
                $formatted = $this->formatterUtil->formatDcaFieldValue(
                    $dc,
                    $field,
                    $raw,
                    FormatDcaFieldValueOptions::create()
                        ->setLoadDca(false)
                        ->setReplaceInsertTags(false)
                        ->setArrayJoiner(self::ARRAY_JOINER)
                );
            } catch (\Throwable $e) {
                $this->logger->warning(
                    \sprintf('Could not resolve field "%s.%s" for the search vector, indexing the raw value: %s', $table, $field, $e->getMessage()),
                    ['exception' => $e]
                );

                $formatted = \is_array($raw) ? \implode(self::ARRAY_JOINER, $raw) : $raw;
            }

            foreach (\explode(self::ARRAY_JOINER, $this->toPlainText((string) $formatted)) as $value) {
                $values[] = $value;
            }
        }

        return $values;
    }

    private function toPlainText(string $value): string
    {
        $value = StringUtil::decodeEntities(StringUtil::restoreBasicEntities($value));

        // Remove insert tags, nested ones from the inside out
        do {
            $value = (string) \preg_replace('/\{\{[^{}]*}}/', ' ', $value, -1, $count);
        } while ($count > 0);

        // Replace every tag with a space, so "<p>Wort.</p><p>Wort</p>" does not collapse into one word
        return (string) \preg_replace('/<[^>]*+>/', ' ', $value);
    }
}

<?php

namespace HeimrichHannot\UtilsBundle\SearchVector;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Util\LocaleUtil;
use Contao\DataContainer;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use HeimrichHannot\UtilsBundle\Event\BuildSearchVectorEvent;
use HeimrichHannot\UtilsBundle\Util\FormatterUtil;
use HeimrichHannot\UtilsBundle\Util\FormatterUtil\FormatDcaFieldValueOptions;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
        private readonly ContaoFramework $framework,
        private readonly TranslatorInterface $translator,
    ) {}

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
        $locale = $this->configurationResolver->getRecordLocale($configuration, $row);
        $dc ??= new SearchVectorDataContainer($table, $row);

        $values = $this->runInLanguage($locale, $table, fn (): array => $this->formatValues($table, $row, $configuration->getFields(), $dc));

        $event = $this->eventDispatcher->dispatch(new BuildSearchVectorEvent($table, $row, $values, $locale));

        $parts = [];

        foreach ($event->getValues() as $value) {
            if ($tokens = Tokenizer::tokenize($value, $locale, $stemmer)) {
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

    /**
     * Runs the callback with the Contao language, all loaded language files and the translator switched to the given
     * language, and restores the previous language afterwards.
     *
     * Language files loaded by the callback, e.g. in an options callback, are restored as well, so the current user
     * never ends up with labels in the language of the record.
     *
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     */
    private function runInLanguage(string $locale, string $table, callable $callback): mixed
    {
        $language = LocaleUtil::formatAsLanguageTag($locale);
        $previousLanguage = $GLOBALS['TL_LANGUAGE'] ?? null;

        if ($language === $previousLanguage) {
            return $callback();
        }

        $controller = $this->framework->getAdapter(Controller::class);
        $translator = $this->translator instanceof LocaleAwareInterface ? $this->translator : null;
        $previousTranslatorLocale = $translator?->getLocale();

        // Language files are loaded in $GLOBALS['TL_LANGUAGE'], the formatter reloads the default one on every call.
        // Labels are read from the language files of any table, e.g. through a reference to another table.
        $reloadLanguageFiles = static function () use ($controller, $table): void {
            foreach (\array_unique([...LoadedLanguageFiles::getNames(), 'default', $table]) as $name) {
                $controller->loadLanguageFile($name);
            }
        };

        $GLOBALS['TL_LANGUAGE'] = $language;
        $reloadLanguageFiles();
        $translator?->setLocale(LocaleUtil::formatAsLocale($locale));

        try {
            return $callback();
        } finally {
            if (null !== $previousTranslatorLocale) {
                $translator->setLocale($previousTranslatorLocale);
            }

            if (null === $previousLanguage) {
                unset($GLOBALS['TL_LANGUAGE']);
            } else {
                $GLOBALS['TL_LANGUAGE'] = $previousLanguage;
            }

            $reloadLanguageFiles();
        }
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

<?php

namespace HeimrichHannot\UtilsBundle\SearchVector;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Builds search conditions against the search vector of a table.
 *
 * Query syntax: commas separate alternatives (OR), the words of one alternative must all match (AND), words wrapped
 * in double quotes must match as a phrase. Words match as substrings of the stored stems, so compounds are found by
 * their parts ("Württemberg" finds "Baden-Württemberg").
 *
 * The search is stemmed in the language the records were indexed in. For tables with a language field it defaults to
 * the language of the current request, pass $locale to search in another language.
 *
 * Matches can be sorted by relevance: a word at the start of a word scores 1, a word that is a whole word 1 more, and
 * the words of an alternative standing next to each other in the order of the search score 2 per word on top. Words of
 * quoted phrases count individually. So "Dresdner Stollen" ranks records with both words in a row before records with
 * the words in different places, and "Honig" ranks "Honig" before "Imkerhonig".
 */
class SearchVectorQuery
{
    /**
     * Escape character of the LIKE patterns. Unlike a backslash, it needs no escaping in SQL string literals and is not
     * a default escape character anywhere, so the explicit ESCAPE clause works the same on MySQL, SQLite and PostgreSQL.
     */
    private const LIKE_ESCAPE = '!';

    public function __construct(
        private readonly Connection $connection,
        private readonly SearchVectorConfigurationResolver $configurationResolver,
    ) {}

    /**
     * Adds the search condition to the query builder with andWhere().
     *
     * @param string|null $alias  The table alias used in the query, defaults to the table name
     * @param string|null $locale The language of the search, see SearchVectorConfigurationResolver::getSearchLocale()
     *
     * @return bool False if the search contains no searchable words. The query builder is not changed then.
     */
    public function apply(QueryBuilder $queryBuilder, string $table, string $search, ?string $alias = null, ?string $locale = null): bool
    {
        [$column, $phrases] = $this->prepare($table, $search, $alias, $locale);

        if (!$phrases) {
            return false;
        }

        $expr = $queryBuilder->expr();
        $alternatives = [];

        foreach ($phrases as $terms) {
            $conditions = [];

            foreach ($terms as $term) {
                $conditions[] = $expr->like(
                    $column,
                    $queryBuilder->createNamedParameter('%' . $this->escapeLike($term) . '%'),
                    $this->connection->quote(self::LIKE_ESCAPE)
                );
            }

            $alternatives[] = $expr->and(...$conditions);
        }

        $queryBuilder->andWhere($expr->or(...$alternatives));

        return true;
    }

    /**
     * Creates columns and values for Contao model finders, e.g. `ExampleModel::findBy($columns, $values)`.
     *
     * @param string|null $locale The language of the search, see SearchVectorConfigurationResolver::getSearchLocale()
     *
     * @return array{0: list<string>, 1: list<string>} Both lists are empty if the search contains no searchable
     *                                                 words. Check that before querying, an empty list matches every
     *                                                 record.
     */
    public function createModelCriteria(string $table, string $search, ?string $locale = null): array
    {
        [$column, $phrases] = $this->prepare($table, $search, null, $locale);

        if (!$phrases) {
            return [[], []];
        }

        $values = [];
        $condition = $this->buildCondition($column, $phrases, static function (string $value) use (&$values): string {
            $values[] = $value;

            return '?';
        });

        return [[$condition], $values];
    }

    /**
     * Creates the search condition as SQL with named parameters, for query abstractions other than the Doctrine
     * query builder.
     *
     * @param string|null $alias           The table alias used in the query, defaults to the table name
     * @param string|null $locale          The language of the search, see SearchVectorConfigurationResolver::getSearchLocale()
     * @param string      $parameterPrefix The prefix of the parameter names, use different prefixes to combine several
     *                                     conditions in one query
     *
     * @return array{0: string, 1: array<string, string>}|null The condition and its parameters by name (without
     *                                                         colon), null if the search contains no searchable words
     */
    public function createCondition(string $table, string $search, ?string $alias = null, ?string $locale = null, string $parameterPrefix = 'searchVector'): ?array
    {
        $placeholder = $this->createNamedPlaceholder($parameterPrefix, $parameters);
        [$column, $phrases] = $this->prepare($table, $search, $alias, $locale);

        if (!$phrases) {
            return null;
        }

        return [$this->buildCondition($column, $phrases, $placeholder), $parameters];
    }

    /**
     * Sorts the query by relevance with addOrderBy(), see the class description. Call it before adding other sort
     * orders, which then sort records of the same relevance. Use it together with apply(), it does not filter.
     *
     * @param string|null $alias  The table alias used in the query, defaults to the table name
     * @param string|null $locale The language of the search, see SearchVectorConfigurationResolver::getSearchLocale()
     *
     * @return bool False if the search contains no searchable words. The query builder is not changed then.
     */
    public function orderByRelevance(QueryBuilder $queryBuilder, string $table, string $search, ?string $alias = null, ?string $locale = null): bool
    {
        [$column, $words] = $this->prepare($table, $search, $alias, $locale, words: true);

        if (!$words) {
            return false;
        }

        $queryBuilder->addOrderBy($this->buildRelevance($column, $words, $queryBuilder->createNamedParameter(...)), 'DESC');

        return true;
    }

    /**
     * Creates the relevance of the search as SQL with named parameters, for query abstractions other than the
     * Doctrine query builder. The expression returns a number, higher is more relevant, see the class description.
     *
     * @param string|null $alias           The table alias used in the query, defaults to the table name
     * @param string|null $locale          The language of the search, see SearchVectorConfigurationResolver::getSearchLocale()
     * @param string      $parameterPrefix The prefix of the parameter names, use different prefixes to combine several
     *                                     expressions in one query
     *
     * @return array{0: string, 1: array<string, string>}|null The expression and its parameters by name (without
     *                                                         colon), null if the search contains no searchable words
     */
    public function createRelevance(string $table, string $search, ?string $alias = null, ?string $locale = null, string $parameterPrefix = 'searchVectorRelevance'): ?array
    {
        $placeholder = $this->createNamedPlaceholder($parameterPrefix, $parameters);
        [$column, $words] = $this->prepare($table, $search, $alias, $locale, words: true);

        if (!$words) {
            return null;
        }

        return [$this->buildRelevance($column, $words, $placeholder), $parameters];
    }

    /**
     * @param list<list<string>>        $phrases
     * @param callable(string): string $placeholder Receives the LIKE pattern and returns its placeholder
     */
    private function buildCondition(string $column, array $phrases, callable $placeholder): string
    {
        $alternatives = [];

        foreach ($phrases as $terms) {
            $conditions = [];

            foreach ($terms as $term) {
                $conditions[] = $column . ' LIKE ' . $placeholder('%' . $this->escapeLike($term) . '%') . ' ESCAPE ' . $this->connection->quote(self::LIKE_ESCAPE);
            }

            $alternatives[] = '(' . \implode(' AND ', $conditions) . ')';
        }

        return '(' . \implode(' OR ', $alternatives) . ')';
    }

    /**
     * Every word of the vector is preceded and followed by a space, so " word" matches at the start of a word and
     * " word " a whole word.
     *
     * Scores the words of each alternative, not its filter terms: the words of a quoted phrase count individually, and
     * repeated words stay in the sequence that must stand in a row.
     *
     * @param list<list<string>>        $alternatives The words of each alternative, see Tokenizer::parseQueryWords()
     * @param callable(string): string $placeholder  Receives the LIKE pattern and returns its placeholder
     */
    private function buildRelevance(string $column, array $alternatives, callable $placeholder): string
    {
        $score = fn (string $pattern, int $points): string => 'CASE WHEN ' . $column . ' LIKE ' . $placeholder($pattern)
            . ' ESCAPE ' . $this->connection->quote(self::LIKE_ESCAPE) . ' THEN ' . $points . ' ELSE 0 END';

        $scores = [];

        foreach ($alternatives as $words) {
            $words = \array_map($this->escapeLike(...), $words);

            foreach (\array_unique($words) as $word) {
                $scores[] = $score('% ' . $word . '%', 1);
                $scores[] = $score('% ' . $word . ' %', 1);
            }

            if (\count($words) > 1) {
                $scores[] = $score('% ' . \implode(' ', $words) . '%', 2 * \count($words));
            }
        }

        return '(' . \implode(' + ', $scores) . ')';
    }

    /**
     * @param array<string, string>|null $parameters Receives the parameters by name
     *
     * @return callable(string): string
     */
    private function createNamedPlaceholder(string $parameterPrefix, ?array &$parameters): callable
    {
        if (!\preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $parameterPrefix)) {
            throw new \InvalidArgumentException(\sprintf('Invalid parameter prefix "%s".', $parameterPrefix));
        }

        $parameters = [];

        return static function (string $value) use (&$parameters, $parameterPrefix): string {
            $name = $parameterPrefix . '_' . \count($parameters);
            $parameters[$name] = $value;

            return ':' . $name;
        };
    }

    /**
     * @param bool $words Whether to parse the words of each phrase for the relevance (Tokenizer::parseQueryWords())
     *                    instead of the filter terms (Tokenizer::parseQuery())
     *
     * @return array{0: string, 1: list<list<string>>} The quoted column and the parsed search phrases
     */
    private function prepare(string $table, string $search, ?string $alias, ?string $locale, bool $words = false): array
    {
        $configuration = $this->configurationResolver->getConfiguration($table);
        $arguments = [
            $search,
            $this->configurationResolver->getSearchLocale($configuration, $locale),
            $this->configurationResolver->getStemmer($configuration),
            $this->configurationResolver->getNormalizer($configuration),
        ];

        $platform = $this->connection->getDatabasePlatform();
        $column = $platform->quoteSingleIdentifier($alias ?? $table) . '.' . $platform->quoteSingleIdentifier($configuration->getFieldName());

        return [$column, $words ? Tokenizer::parseQueryWords(...$arguments) : Tokenizer::parseQuery(...$arguments)];
    }

    /**
     * Escapes the LIKE wildcards in a term, which custom normalizers or stemmers might produce.
     */
    private function escapeLike(string $term): string
    {
        $escape = self::LIKE_ESCAPE;

        return \strtr($term, [$escape => $escape . $escape, '%' => $escape . '%', '_' => $escape . '_']);
    }
}

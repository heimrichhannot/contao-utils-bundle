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
        if (!\preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $parameterPrefix)) {
            throw new \InvalidArgumentException(\sprintf('Invalid parameter prefix "%s".', $parameterPrefix));
        }

        [$column, $phrases] = $this->prepare($table, $search, $alias, $locale);

        if (!$phrases) {
            return null;
        }

        $parameters = [];
        $condition = $this->buildCondition($column, $phrases, static function (string $value) use (&$parameters, $parameterPrefix): string {
            $name = $parameterPrefix . '_' . \count($parameters);
            $parameters[$name] = $value;

            return ':' . $name;
        });

        return [$condition, $parameters];
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
     * @return array{0: string, 1: list<list<string>>} The quoted column and the parsed search phrases
     */
    private function prepare(string $table, string $search, ?string $alias, ?string $locale): array
    {
        $configuration = $this->configurationResolver->getConfiguration($table);
        $phrases = Tokenizer::parseQuery(
            $search,
            $this->configurationResolver->getSearchLocale($configuration, $locale),
            $this->configurationResolver->getStemmer($configuration),
            $this->configurationResolver->getNormalizer($configuration)
        );

        return [$this->connection->quoteIdentifier(($alias ?? $table) . '.' . $configuration->getFieldName()), $phrases];
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

<?php

namespace HeimrichHannot\UtilsBundle\SearchVector;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Builds search conditions against the search vector of a table.
 *
 * Query syntax: commas separate alternatives (OR), the words of one alternative must all match (AND), words wrapped
 * in double quotes must match as a phrase. Words match as substrings of the stored stems, so compounds are found by
 * their parts ("Oberpfalz" finds "Niederbayern-Oberpfalz").
 *
 * The search is stemmed in the language the records were indexed in. For tables with a language field it defaults to
 * the language of the current request, pass $locale to search in another language.
 */
class SearchVectorQuery
{
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
                $conditions[] = $expr->like($column, $queryBuilder->createNamedParameter('%' . $this->escapeLike($term) . '%'));
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

        $alternatives = [];
        $values = [];

        foreach ($phrases as $terms) {
            $conditions = [];

            foreach ($terms as $term) {
                $conditions[] = $column . ' LIKE ?';
                $values[] = '%' . $this->escapeLike($term) . '%';
            }

            $alternatives[] = '(' . \implode(' AND ', $conditions) . ')';
        }

        if (!$alternatives) {
            return [[], []];
        }

        return [['(' . \implode(' OR ', $alternatives) . ')'], $values];
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
            $this->configurationResolver->getStemmer($configuration)
        );

        return [$this->connection->quoteIdentifier(($alias ?? $table) . '.' . $configuration->getFieldName()), $phrases];
    }

    private function escapeLike(string $term): string
    {
        return \addcslashes($term, '%_\\');
    }
}

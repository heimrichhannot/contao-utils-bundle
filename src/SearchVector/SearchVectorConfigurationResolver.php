<?php

namespace HeimrichHannot\UtilsBundle\SearchVector;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use HeimrichHannot\UtilsBundle\Dca\SearchVectorField;
use HeimrichHannot\UtilsBundle\Dca\SearchVectorFieldConfiguration;
use HeimrichHannot\UtilsBundle\SearchVector\Stemmer\StemmerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Looks up the search vector registration of a table, the stemmer it uses and the language to index and search in.
 *
 * @internal
 */
class SearchVectorConfigurationResolver
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly ContainerInterface $searchVectorStemmers,
        private readonly RequestStack $requestStack,
        private readonly string $defaultLocale,
    ) {}

    /**
     * @throws \InvalidArgumentException if the table has no search vector field
     */
    public function getConfiguration(string $table): SearchVectorFieldConfiguration
    {
        // The registration happens in the DCA file of the table
        $this->framework->initialize();
        $this->framework->getAdapter(Controller::class)->loadDataContainer($table);

        return SearchVectorField::getRegistrations()[$table]
            ?? throw new \InvalidArgumentException(\sprintf('Table "%s" has no search vector field.', $table));
    }

    public function getStemmer(SearchVectorFieldConfiguration $configuration): ?StemmerInterface
    {
        if (null === $configuration->getStemmer()) {
            return null;
        }

        if (!$this->searchVectorStemmers->has($configuration->getStemmer())) {
            throw new \InvalidArgumentException(\sprintf(
                'Search vector stemmer "%s" of table "%s" is not a registered service implementing %s.',
                $configuration->getStemmer(),
                $configuration->getTable(),
                StemmerInterface::class
            ));
        }

        return $this->searchVectorStemmers->get($configuration->getStemmer());
    }

    /**
     * The language of a record: the value of the language field, else the configured language, else the default
     * locale. Never the language of the current user, so the vector does not depend on who saved the record.
     *
     * @param array<string, mixed> $row
     */
    public function getRecordLocale(SearchVectorFieldConfiguration $configuration, array $row): string
    {
        $languageField = $configuration->getLanguageField();

        return ($languageField ? (string) ($row[$languageField] ?? '') : '')
            ?: $configuration->getLanguage()
            ?: $this->defaultLocale;
    }

    /**
     * The language a search is stemmed in, so search terms match the indexed stems.
     *
     * Tables with records in one language are always searched in that language. Tables with a language field are
     * searched in the language of the current request, as visitors usually search content in their own language.
     */
    public function getSearchLocale(SearchVectorFieldConfiguration $configuration, ?string $locale = null): string
    {
        if (!$locale && $configuration->getLanguageField()) {
            $locale = $this->requestStack->getCurrentRequest()?->getLocale();
        }

        return $locale ?: $configuration->getLanguage() ?: $this->defaultLocale;
    }
}

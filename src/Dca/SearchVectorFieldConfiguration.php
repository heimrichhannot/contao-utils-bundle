<?php

namespace HeimrichHannot\UtilsBundle\Dca;

class SearchVectorFieldConfiguration extends DcaFieldConfiguration
{
    /**
     * @var list<string>
     */
    protected array $fields = [];
    protected string $fieldName = 'searchVector';
    protected ?string $stemmer = null;
    protected ?string $language = null;
    protected ?string $languageField = null;

    /**
     * The fields whose values are written to the search vector. Values are resolved like in the backend
     * (options, references, foreign keys, …).
     *
     * @param list<string> $fields
     */
    public function setFields(array $fields): SearchVectorFieldConfiguration
    {
        $this->fields = \array_values($fields);
        return $this;
    }

    /**
     * @return list<string>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFieldName(string $fieldName): SearchVectorFieldConfiguration
    {
        $this->fieldName = $fieldName;
        return $this;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * The service id of a StemmerInterface implementation (usually its class name), e.g. SnowballStemmer::class.
     * Stemming is disabled by default. Rebuild the search vectors after changing the stemmer.
     */
    public function setStemmer(?string $stemmer): SearchVectorFieldConfiguration
    {
        $this->stemmer = $stemmer;
        return $this;
    }

    public function getStemmer(): ?string
    {
        return $this->stemmer;
    }

    /**
     * The language of the records, e.g. "de". Used for stemming and for resolving option labels, independent of the
     * language of the current user. Falls back to the default locale of the application (kernel.default_locale),
     * which is often "en" even on German websites, so set it explicitly for tables with records in one language.
     */
    public function setLanguage(?string $language): SearchVectorFieldConfiguration
    {
        $this->language = $language;
        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    /**
     * The field holding the language of a record, for tables with records in different languages. Records with an
     * empty value fall back to the language set with setLanguage().
     */
    public function setLanguageField(?string $languageField): SearchVectorFieldConfiguration
    {
        $this->languageField = $languageField;
        return $this;
    }

    public function getLanguageField(): ?string
    {
        return $this->languageField;
    }
}

# Search vector field

The search vector field holds the values of other fields as normalized words, so records can be searched by everything a
visitor might type: option labels instead of option keys, the titles of related records, words without accents and,
with a stemmer, inflected words.

## Setup

Register the field in the DCA of your table and list the fields to make searchable:

```php
# contao/dca/tl_example.php
use HeimrichHannot\UtilsBundle\Dca\SearchVectorField;

SearchVectorField::register('tl_example')
    ->setFields(['title', 'teaser', 'category', 'author'])
    ->setLanguage('de')
;
```

Then update the database (`contao:migrate`) and build the vectors of the existing records:

```bash
vendor/bin/contao-console huh:utils:search-vector:rebuild tl_example
```

## Configuration

```php
# contao/dca/tl_example.php
use HeimrichHannot\UtilsBundle\Dca\SearchVectorField;
use HeimrichHannot\UtilsBundle\SearchVector\Normalizer\GermanNormalizer;
use HeimrichHannot\UtilsBundle\SearchVector\Stemmer\SnowballStemmer;

SearchVectorField::register('tl_example')
    ->setFields(['title', 'teaser'])
    ->setFieldName('searchVector') // the field name (default: searchVector)
    ->setStemmer(SnowballStemmer::class) // the stemmer service (default: null, no stemming)
    ->setNormalizer(GermanNormalizer::class) // the normalizer service (default: null, no normalization)
    ->setLanguage('de') // the language of the records (default: the default locale of the application)
    ->setLanguageField('language') // the field holding the language of a record, for tables with records in different languages
    ->setAsync(false) // rebuild the vector right away when saving in the backend (default: true, asynchronously)
;
```

Rebuild the vectors after changing the configuration.

Only one search vector field per table is supported. Registering a second one for the same table throws an exception
when the DCA of the table is loaded.

## Field values

Field values are resolved like in the backend: options and references become their labels, foreign keys the title of
the related record, HTML is reduced to text and insert tags are removed. Each value of a multiple field stays a separate
section of the vector, so a phrase never matches across two values.

## Language

Option labels are resolved and words are stemmed in the language of the record, never in the language of the user who
saves it. The language is taken from the language field, then from `setLanguage()`, then from the default locale
(`kernel.default_locale`).

> [!IMPORTANT]
> Many Contao installations keep the Symfony default locale `en`, even for German websites. Set the language of the
> records with `setLanguage()`, otherwise option labels are indexed in English and German words are stemmed with
> English rules.

## Searching

Search with the `SearchVectorQuery` service, either on a Doctrine query builder or with Contao model criteria:

```php
use HeimrichHannot\UtilsBundle\SearchVector\SearchVectorQuery;

// Doctrine query builder, pass the table alias as fourth argument if you use one
$searchVectorQuery->apply($queryBuilder, 'tl_example', $search);

// search in a specific language
$searchVectorQuery->apply($queryBuilder, 'tl_example', $search, locale: 'en');

// Contao model
[$columns, $values] = $searchVectorQuery->createModelCriteria('tl_example', $search);

if ($columns) {
    $models = ExampleModel::findBy($columns, $values);
}
```

For other query abstractions, `createCondition()` returns the condition as SQL with named parameters:

```php
// pass the table alias as third argument if you use one, and a parameter prefix to combine several conditions
$condition = $searchVectorQuery->createCondition('tl_example', $search, 'e', parameterPrefix: 'search');

if ($condition) {
    // e.g. '(("e"."searchVector" LIKE :search_0 ESCAPE '!'))' and ['search_0' => '%museum%']
    [$sql, $parameters] = $condition;
}
```

`apply()` returns `false`, `createModelCriteria()` returns empty lists and `createCondition()` returns `null` if the
search contains no searchable words. Check that before querying, empty criteria match every record.

The search is stemmed in the language of the records. For tables with a language field it defaults to the language of
the current request, so pass the locale if you search records of another language.

Search syntax: commas separate alternatives, all words of one alternative must match, and words wrapped in double
quotes must match as a phrase. For example `altstadt führung, museum "am abend"` finds records containing both
"altstadt" and "führung", or "museum" together with the phrase "am abend". Words also match parts of compound words,
e.g. "Württemberg" finds "Baden-Württemberg".

### Relevance

The words of an alternative may be found anywhere in a record, so "Dresdner Stollen" also finds a record mentioning
"Dresdner" in one sentence and "Stollen" in another. Sort by relevance to list the best matches first:

```php
// Doctrine query builder: call it before adding other sort orders, which then sort records of the same relevance
$searchVectorQuery->apply($queryBuilder, 'tl_example', $search);
$searchVectorQuery->orderByRelevance($queryBuilder, 'tl_example', $search);
$queryBuilder->addOrderBy('title');

// other query abstractions: a numeric SQL expression with named parameters, higher is more relevant
[$sql, $parameters] = $searchVectorQuery->createRelevance('tl_example', $search, 'e', parameterPrefix: 'relevance');
```

A word scores 1 if it matches at the start of a word and 1 more if it matches a whole word. If all words of an
alternative stand next to each other in the order of the search, they score 2 per word on top. Words of quoted phrases
count individually, and repeated words are part of that order. So "Dresdner Stollen" lists records with both words in a
row first, and "Honig" lists "Honig" before "Honigwein" and "Imkerhonig". The scores of alternatives add up.

## Keeping the vectors up to date

The vector is rebuilt when a record is saved, copied or restored from a version in the backend. The rebuild runs
asynchronously, so saving does not wait for it. A message is sent to the `contao_prio_normal` messenger transport of the
Contao Managed Edition and processed by a messenger worker, or by Contao's web worker at the end of the request if no
worker is running. The vector is usually up to date a few seconds later. Without that transport, or with
`setAsync(false)`, the vector is rebuilt right away.

Records saved outside the backend (frontend forms, imports, …) need to be indexed by your code:

```php
use HeimrichHannot\UtilsBundle\SearchVector\SearchVectorIndexer;

// right away
$searchVectorIndexer->index('tl_example', $id);

// asynchronously like in the backend, right away if the messenger transport is not available
$searchVectorIndexer->queue('tl_example', $id);
```

Rebuild the vectors of a table whenever something outside a record save changes what should be found, e.g. renamed
options or related records:

```bash
vendor/bin/contao-console huh:utils:search-vector:rebuild tl_example
vendor/bin/contao-console huh:utils:search-vector:rebuild tl_example --id=12 --id=13
```

## Additional values

Values not stored in a column of the record, for example tags or other relations kept in a separate table, can be added
with the `BuildSearchVectorEvent`:

```php
use HeimrichHannot\UtilsBundle\Event\BuildSearchVectorEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class AddTagsToSearchVectorListener
{
    public function __invoke(BuildSearchVectorEvent $event): void
    {
        if ('tl_example' !== $event->table) {
            return;
        }

        foreach ($this->findTagNames((int) $event->row['id']) as $tagName) {
            $event->addValue($tagName);
        }
    }
}
```

## Stemming

Stemming reduces words to their stem, so "Häuser" also finds "Haus". It is disabled by default. We recommend
[wamania/php-stemmer](https://github.com/wamania/php-stemmer), a native PHP implementation of the
[Snowball](https://snowballstem.org/) stemmers for Catalan, Danish, Dutch, English, Finnish, French, German, Italian,
Norwegian, Portuguese, Romanian, Russian, Spanish and Swedish. The bundle ships a ready-made stemmer for it:

```bash
composer require wamania/php-stemmer
```

```php
# contao/dca/tl_example.php
use HeimrichHannot\UtilsBundle\Dca\SearchVectorField;
use HeimrichHannot\UtilsBundle\SearchVector\Stemmer\SnowballStemmer;

SearchVectorField::register('tl_example')
    ->setFields(['title', 'teaser'])
    ->setStemmer(SnowballStemmer::class)
;
```

Each word is stemmed with the rules of the record language, words of unsupported languages stay unchanged.

A stemmer may cut a compound differently than its last word on its own, e.g. "Imkerhonig" becomes "imkerhon" while
"Honig" stays "honig". The unstemmed words of each value are therefore stored as well, so "Honig" still finds
"Imkerhonig". This makes the vector longer, up to twice the length without a stemmer.

To use another stemmer, implement `HeimrichHannot\UtilsBundle\SearchVector\Stemmer\StemmerInterface` as an
autoconfigured service and pass its service id to `setStemmer()`. The stemmer gets each word lowercased with its accents
and the language of the word, and should return words of languages it does not support unchanged.

## Normalization

A normalizer unifies different spellings of the same word before stemming. It is disabled by default. The bundle ships
`GermanNormalizer`, which restores German umlauts written as "ae", "oe" and "ue", so "Muenchen", "München" and
"Munchen" find each other. It follows the rules of Lucene's `GermanNormalizationFilter`: "ue" after a vowel or "q" is
kept, so "Feuer", "Bauer" and "Quelle" stay unchanged. Words of other languages stay unchanged as well.

```php
# contao/dca/tl_example.php
use HeimrichHannot\UtilsBundle\Dca\SearchVectorField;
use HeimrichHannot\UtilsBundle\SearchVector\Normalizer\GermanNormalizer;
use HeimrichHannot\UtilsBundle\SearchVector\Stemmer\SnowballStemmer;

SearchVectorField::register('tl_example')
    ->setFields(['title', 'teaser'])
    ->setNormalizer(GermanNormalizer::class)
    ->setStemmer(SnowballStemmer::class)
;
```

Normalization works with and without stemming.

To use another normalizer, implement `HeimrichHannot\UtilsBundle\SearchVector\Normalizer\NormalizerInterface` as an
autoconfigured service and pass its service id to `setNormalizer()`. Like a stemmer, it gets each word lowercased with
its accents and the language of the word, and should return words of languages it does not support unchanged.

## Limitations

- A reference to the language file of another table is only resolved if that file has been loaded, as in the backend.
  Load it in your DCA if you reference it.
- Options callbacks that cache translated labels in static properties are not switched to the language of the record.
- The search uses `LIKE '%term%'`, which scans the column. That is fine for tables with tens of thousands of records.

# Contao Utils Bundle

[![](https://img.shields.io/packagist/v/heimrichhannot/contao-utils-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-utils-bundle)
[![](https://img.shields.io/packagist/dt/heimrichhannot/contao-utils-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-utils-bundle)
![example branch parameter](https://github.com/heimrichhannot/contao-utils-bundle/actions/workflows/ci.yml/badge.svg?branch=v3)
[![Coverage Status](https://coveralls.io/repos/github/heimrichhannot/contao-utils-bundle/badge.svg?branch=v3)](https://coveralls.io/github/heimrichhannot/contao-utils-bundle?branch=v3)

> Hi, you're looking on a very new version of utils bundle, version 3! See [CHANGELOG.md](CHANGELOG.md) for more information! If you're looking for version 2, please check the [v2 branch](https://github.com/heimrichhannot/contao-utils-bundle/tree/v2).

Utils Bundle is a collection of many small helper to solve repeating task. 
At the center there is a utils service allow access to all util function. 
In addition, there are DcaField helpers, the Entity finder command and some nice twig filters.

## Features
* Utils-Service - A service allow access all bundled utils functions. 
* DcaField registration - An nice api to add typical dca fields to your dca fields without repeating yourself or annoying order restrictions.
  * AuthorField - Add an author field with automatic filling of the default value and optional frontend member support
  * DateAddedField - Add a date added field to store the date of entity creation
  * SearchVectorField - Add a field that makes entities searchable by inflected words, option labels and related records
* Entity Finder - A command to search for any contao entities in your database.
* Twig Filters

## Install

Just install it via composer or contao manager:

```
composer require heimrichhannot/contao-utils-bundle
```

## Usage

### Utils service

The Utils service is the core functionality of this bundle. It provides access to a lot of util functions help solving recurring tasks. 
It's build as one service from which you can access all utils services. 
The utils service is best used with dependency injection, but is also available from the service container as public service for usage in legacy code.
You can check the [API Documentation](https://heimrichhannot.github.io/contao-utils-bundle/namespaces/heimrichhannot-utilsbundle-util.html) to see all available functions.

```php
use HeimrichHannot\UtilsBundle\Util\Utils;

/**
 * A class containing examples usage of utils services. Please don't expect it to be useful :)
 */
class MyClass{
   /** @var Utils */
   protected $utils;
    
   public function __construct(Utils $utils) {
       $this->utils = $utils;
   }
   
   public function someActions(): bool {
        $dcaFields = $this->utils->dca()->getDcaFields('tl_content');
        $this->utils->array()->removeValue('headline', $dcaFields);
        foreach ($dcaFields as $dcaField) {
            echo $this->utils->string()->camelCaseToDashed($dcaField);
        }
        
        $rootPageModel = $this->utils->request()->getCurrentRootPageModel();
        echo $this->utils->anonymize()->anonymizeEmail($rootPageModel->adminEmail);
        
        $groupUsers = $this->utils->user()->findActiveUsersByGroup([1,2]);
        $this->utils->url()->addQueryStringParameterToUrl('user='.$groupUsers[0]->username, 'https://example.org');
        
        if ($this->utils->container()->isBackend()) {
            $where = $this->utils->database()->createWhereForSerializedBlob('dumbData', ['foo', 'bar']);
            $model = $this->utils->model()->findOneModelInstanceBy('tl_content', [$where->createAndWhere()], [$where->values]);
            echo '<div '.$this->utils->html()->generateAttributeString($model->getHtmlAttributes()).'></div>';
        }
}
```

### Static Utils

Static helper methods that do not need to be injected are provided by the `SUtils` locator.

At this time, the following static helpers are available:
```php
use HeimrichHannot\UtilsBundle\StaticUtil\SUtils;

SUtils::array()->insertBeforeKey($array, $keys, $newKey, $newValue);
SUtils::array()->insertAfterKey($array, $key, $value, $newKey = null, $options = []);
$foundAndRemoved = SUtils::array()->removeValue($value, $array);

SUtils::class()->hasTrait($class, $trait);
```

### Dca Fields

The bundle provides some common dca fields that can be used in your dca files.

#### Author field

Add an author field to your dca. It will be initialized with the current backend user. On copy, it will be set to the current user.

```php
# contao/dca/tl_example.php
use HeimrichHannot\UtilsBundle\Dca\AuthorField;

AuthorField::register('tl_example');
```

You can pass additional options to adjust the field:

```php
# contao/dca/tl_example.php
use HeimrichHannot\UtilsBundle\Dca\AuthorField;

AuthorField::register('tl_example')
    ->setType(AuthorField::TYPE_MEMBER) // can be one of TYPE_USER (default) or TYPE_MEMBER. Use TYPE_MEMBER to set a frontend member instead of a backend user
    ->setFieldNamePrefix('example') // custom prefix for the field name
    ->setUseDefaultLabel(false) // set to false to disable the default label and set a custom label in your dca translations
    ->setExclude(false) // set the dca field exclude option
    ->setFilter(false) // set the dca field filter option
    ->setSearch(false) // set the dca field search option
    ->setSorting(false) // set the dca field sorting option
;
```

#### Date added field

Add a date added field to your dca. It will set a timestamp on create or copy.

```php
# contao/dca/tl_example.php
use HeimrichHannot\UtilsBundle\Dca\DateAddedField;

DateAddedField::register('tl_example');
```

You can pass additional options to adjust the field:

```php
# contao/dca/tl_example.php
use HeimrichHannot\UtilsBundle\Dca\DateAddedField;

DateAddedField::register('tl_example')
    ->setExclude(false) // set the dca field exclude option
    ->setFilter(false) // set the dca field filter option
    ->setSearch(false) // set the dca field search option
    ->setSorting(true) // set the dca field sorting option
;
```

#### Alias field

Add an alias field to your dca. Set the alias if the alias field is empty on save and check for duplicates.

```php
use HeimrichHannot\UtilsBundle\Dca\AliasField;

AliasField::register('tl_example');
```

You can pass additional options to adjust the field:
```php
# contao/dca/tl_example.php
use HeimrichHannot\UtilsBundle\Dca\AliasField;

AliasField::register('tl_example')
    AliasField::register('tl_md_newsletter')
    ->setAliasExistCallback([NewsletterCallbackListener::class, 'checkAliasExist']) // set a custom alias exist callback
    ->setExclude(false) // set the dca field exclude option
    ->setSearch(false) // set the dca field search option
    ->setFilter(false) // set the dca field filter option
    ->setSorting(true) // set the dca field sorting option
    ->setFlag(null) // set the dca field flag option
;
```

#### Search vector field

Add a field that holds the values of other fields as normalized, stemmed words, so records can be searched by
everything a visitor might type: inflected words, option labels instead of option keys, the titles of related records
and so on.

```php
# contao/dca/tl_example.php
use HeimrichHannot\UtilsBundle\Dca\SearchVectorField;

SearchVectorField::register('tl_example')
    ->setFields(['title', 'teaser', 'category', 'author']) // the fields to make searchable
;
```

Field values are resolved like in the backend: options and references become their labels, foreign keys the title of
the related record, HTML is reduced to text and insert tags are removed. The vector is rebuilt when a record is saved,
copied or restored from a version in the backend.

You can pass additional options to adjust the field:

```php
# contao/dca/tl_example.php
use HeimrichHannot\UtilsBundle\Dca\SearchVectorField;

SearchVectorField::register('tl_example')
    ->setFields(['title', 'teaser'])
    ->setFieldName('searchVector') // the field name (default: searchVector)
    ->setStemmer(GermanStemmer::class) // the stemmer service (default: GermanStemmer), pass null to disable stemming
    ->setLanguage('de') // the language of the records (default: the default locale of the application)
    ->setLanguageField('language') // the field holding the language of a record, for tables with records in different languages
;
```

Option labels are resolved and words are stemmed in the language of the record, never in the language of the user who
saves it. The language is taken from the language field, then from `setLanguage()`, then from the default locale
(`kernel.default_locale`). The German stemmer leaves words of other languages unchanged.

Search with the `SearchVectorQuery` service, either on a Doctrine query builder or with Contao model criteria:

```php
use HeimrichHannot\UtilsBundle\SearchVector\SearchVectorQuery;

// Doctrine query builder, pass the table alias as fourth argument if you use one
$searchVectorQuery->apply($queryBuilder, 'tl_example', $search);

// Contao model
[$columns, $values] = $searchVectorQuery->createModelCriteria('tl_example', $search);

// search in a specific language
$searchVectorQuery->apply($queryBuilder, 'tl_example', $search, locale: 'en');
if ($columns) {
    $models = ExampleModel::findBy($columns, $values);
}
```

The search is stemmed in the language of the records. For tables with a language field it defaults to the language of
the current request, so pass the locale if you search records of another language.

Search syntax: commas separate alternatives, all words of one alternative must match, and an alternative wrapped in
double quotes must match as a phrase. For example `bahn winter, "bei nacht"` finds records containing both
"bahn" and "winter", or the phrase "bei nacht". Words also match parts of compound words.

Rebuild the vectors of a table after adding the field, after changing the field configuration and whenever something
outside a record save changes what should be found (renamed options, related records, …):

```bash
vendor/bin/contao-console huh:utils:search-vector:rebuild tl_example
vendor/bin/contao-console huh:utils:search-vector:rebuild tl_example --id=12 --id=13
```

Records saved outside the backend (frontend forms, imports, …) need to be indexed by your code:

```php
use HeimrichHannot\UtilsBundle\SearchVector\SearchVectorIndexer;

$searchVectorIndexer->index('tl_example', $id);
```

Values not stored in a column of the record, for example tags or other relations kept in a separate table, can be
added with the `BuildSearchVectorEvent`:

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

To use another stemmer, implement `HeimrichHannot\UtilsBundle\SearchVector\Stemmer\StemmerInterface` as an
autoconfigured service and pass its service id to `setStemmer()`. The stemmer gets the language of each word and should
return words of languages it does not support unchanged.

### Entity Finder

The entity finder is a command to search for any contao entities in your database.

**[Entity finder](docs/commands/entity_finder.md)**


### Twig Filters

This bundle contains currently one twig filter:

#### anonymize_email

Returns an anonymized email address. max.muster@example.org will be max.****@example.org

```twig
{{ user.email|anonymize_email }}
```

## Notes

### Backwards Compatibility Promise

We try our best to keep this bundle backwards compatible and follow the principle of [semantic versioning](https://semver.org/).

Following aspects are not covered by BC promise:
- Using Utils classes direct instead from Utils service. This is not officially supported and may break your application due internal changes.
- Classes marked as `@internal` or `@experimental`

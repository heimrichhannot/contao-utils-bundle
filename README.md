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
    ->setSearch(false) // set the dca field search option
    ->setFilter(false) // set the dca field filter option
;
```

#### Date added field

Add a date added field to your dca. It will set a timestamp on create or copy.

```php
# contao/dca/tl_example.php
use HeimrichHannot\UtilsBundle\Dca\DateAddedField;

DateAddedField::register('tl_example');
```


### Entity Finder

The entity finder is a command to search for any contao entities in your database.

**[Entity finder](docs/commands/entity_finder.md)**


### Twig Filters

This bundle contains currently one twig filter:

### anonymize_email

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

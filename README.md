# Contao Utils Bundle

[![](https://img.shields.io/packagist/v/heimrichhannot/contao-utils-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-utils-bundle)
[![](https://img.shields.io/packagist/dt/heimrichhannot/contao-utils-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-utils-bundle)
[![Build Status](https://travis-ci.org/heimrichhannot/contao-utils-bundle.svg?branch=master)](https://travis-ci.org/heimrichhannot/contao-utils-bundle)
[![Coverage Status](https://coveralls.io/repos/github/heimrichhannot/contao-utils-bundle/badge.svg?branch=master)](https://coveralls.io/github/heimrichhannot/contao-utils-bundle?branch=master)

This bundle offers various utility functionality for the Contao CMS.

> [!NOTE]
> We are currently maintaining two branches of this bundle.
> 
> - The `v3` branch is the current stable version.
> - The `v2` branch is this legacy version.
> 
> The `v3` branch is a major rewrite of the bundle and has maintained little backwards compatibility with the `v2` branch.
> To aid in migrating to the new version, we are backporting reasonable features from `v3` to `v2`.
> 
> We are providing bugfixes and security updates for the `v2` branch for the time being. 

## Install

1. Install via composer:
    ```
    composer require heimrichhannot/contao-utils-bundle:^2.0
    ```
2. Update database

### Optional Requirements 

Add following dependencies to your project composer file, if you want to use one of the following utils:

| Util                                                  | Dependency                                                             |
|-------------------------------------------------------|------------------------------------------------------------------------|
| [~~PdfCreator - mPDF~~](docs/utils/pdf/pdfcreator.md) | `"mpdf/mpdf": "^7.0\|^8.0"`                                            |
| huh.utils.pdf.preview                                 | `"spatie/pdf-to-image": "^1.8"` or/and `"alchemy/ghostscript": "^4.1"` |

## Usage

### Service Locator

This is a backport and essential feature of the `v3` branch. We recommend to refactor your code to use the `Utils`-service locator, and to only use this pattern in new code. 

> [!NOTE]
> We're currently in a process to move all services into the Utils namespace and make them all accessible from the new Utils service. 

This Bundle is a collection of utils to solve recurring tasks. See the [API Documentation](https://heimrichhannot.github.io/contao-utils-bundle/) for all util classes and methods. 

The "default" way to access utils is the `Utils`-service

> [!NOTE]
> Currently, not all services have been backported to the new service locator and are still only available through service tags.

The utils service is used best with dependency injection, but is also available from the service container as a public service to be used in legacy code.

```php
use HeimrichHannot\UtilsBundle\Util\Utils;

class MyService {
    /** @var Utils */
    protected $utils;
     
    public function __construct(Utils $utils) {
        $this->utils = $utils;
    }
    
    public function someActions(): bool {
        return $this->utils->string()->startsWith('Lorem ipsum dolor sit amet', 'Lorem');
    }
}
```

To access services that are not available through the `Utils` service, inject or call them directly.

> [!IMPORTANT]
> Keep in mind that all utils are on the brink of being moved to the `Utils`-service locator and won't be available through service tags in `v3`.

### Index of Available Services

Available [Services](src/Resources/config/services.yml) (as of version 2.131):

```
huh.utils.accordion
huh.utils.anonymizer
huh.utils.array
huh.utils.cache.database
huh.utils.cache.database_tree
huh.utils.cache.file
huh.utils.cache.remote_image_cache
huh.utils.choice.data_container
huh.utils.choice.field
huh.utils.choice.message
huh.utils.choice.model_instance
huh.utils.choice.twig_template
huh.utils.class
huh.utils.code
huh.utils.comparison
huh.utils.container
huh.utils.database
huh.utils.date
huh.utils.dca
huh.utils.encryption
huh.utils.file
huh.utils.file_archive
huh.utils.folder
huh.utils.form
huh.utils.image
huh.utils.location
huh.utils.member
huh.utils.model
huh.utils.module
huh.utils.pdf.preview
huh.utils.request.curl
huh.utils.routing
huh.utils.salutation
huh.utils.string
huh.utils.template
huh.utils.url
huh.utils.user                                    
```

### Common dca fields

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
    ->setExclude(true) // set the dca field exclude option
    ->setSearch(true) // set the dca field search option
    ->setFilter(true) // set the dca field filter option
    ->setSorting(false) // set the dca field sorting option
    ->setFlag(null) // set the dca field flag option
;
```

#### Date added field

Add a date added field to your dca. It will be initialized with the current date and time. On copy, it will be set to the current date and time.

```php
# contao/dca/tl_example.php
use HeimrichHannot\UtilsBundle\Dca\DateAddedField;

DateAddedField::register('tl_example')
```

You can pass additional options to adjust the field:
```php
# contao/dca/tl_example.php
use HeimrichHannot\UtilsBundle\Dca\DateAddedField;

DateAddedField::register('tl_example')
    ->setExclude(false) // set the dca field exclude option
    ->setSearch(false) // set the dca field search option
    ->setFilter(false) // set the dca field filter option
    ->setSorting(true) // set the dca field sorting option
    ->setFlag(null) // set the dca field flag option
;
```


## Documentation

[API Documentation](https://heimrichhannot.github.io/contao-utils-bundle/)

### Utils 

~~[PdfCreatorx](docs/utils/pdf/pdfcreator.md) - High-level API to create pdf files with PHP~~ (PDFCreator has moved to its [own library](https://github.com/heimrichhannot/pdf-creator).)

### Using with Webpack/Encore

The bundle assets (js) are prepared to be used with webpack/encore. If you don't use [Foxy](https://github.com/fxpio/foxy), you need to add `"@hundh/contao-utils-bundle": "^1.5.0"` to your project package.json. 

See [package Repository](https://github.com/heimrichhannot-contao-components/contao-utils-bundle) for documentation.

### JavaScript Utils

#### Ajax Util
This util offers a shorthand for POST- and GET-ajax-requests. 

```
static post(url, data, config) {...}
# or
static get(url, data, config) {...}
``` 
Both take the following parameters
 
| name   | description                                                                                                   |
|--------|---------------------------------------------------------------------------------------------------------------|
| url    | The url the request will be send to.                                                                          |
| data   | The data that is used for the request. It has to be passed as JSON.                                           |
| config | An object that can hold the onSuccess-, onError-, beforeSubmit-, afterSubmit-callback and the request headers | 
 
 
Parameters of config

| name         | description                                                                                   |
|--------------|-----------------------------------------------------------------------------------------------|
| onSuccess    | Is called when the request was successful. The request is passed as parameter.                |
| onError      | Is called when the request had an error. The request is passed as parameter.                  |
| beforeSubmit | Is called before the request is submitted. The url, data and config are passed as parameters. |
| afterSubmit  | Is called before the request is submitted. The url, data and config are passed as parameters. |
| headers      | The headers will be set when the request is initialized.                                      |
 
The contents of the config parameter are all optional. If you pass an empty config to the ajaxUtil a silent request will be processed.
The data will be transformed in the fitting format accordion wether you use a POST- or a GET-request.

To use the shorthands import the utilsBundle into your script. After that just call the method an pass the needed parameters.

```
import "@hundh/contao-utils-bundle";

// ...

utilsBundle.ajax.get(url, data, config);
// or
utilsBundle.ajax.post(url, data, config);
```

### Configuration

Following configuration parameter can be overridden:

```yaml
# Default configuration for extension with alias: "huh_utils"
huh_utils:
  tmp_folder:           files/tmp/huh_utils_bundle

  # Default folder where to store pdf preview images.
  pdfPreviewFolder:     null
  cache:

    # Enable database tree cache is generated on cache warmup.
    enable_generate_database_tree_cache: false

  # Load utils bundle assets. Default value will be changed to false in next major version.
  enable_load_assets:   true
```

## Twig Extensions

These bundle add server twig filters:

| Filter            | Parameter                                                                               | Description                                                                              |
|-------------------|-----------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------|
| autolink          | array options                                                                           | Create a link if string is an url.                                                       |
| anonymize_email   | -                                                                                       | Returns an anonymized email address. max.muster@example.org will be max.****@example.org |
| deserialize       | bool force_array = false                                                                | Deserialize an serialized array (using `\Contao\StringUtil`)                             |
| download          | download = true, data = [], template = "@HeimrichHannotContaoUtils\/download.html.twig" |                                                                                          |
| download_data     | data = []                                                                               |                                                                                          |
| download_link     | data = []                                                                               |                                                                                          |
| download_path     | data = []                                                                               |                                                                                          |
| download_title    | data = []                                                                               |                                                                                          |
| file_data         | data = [], jsonSerializeOptions = []                                                    |                                                                                          |
| file_path         |                                                                                         |                                                                                          |
| image             | size = null, data = [], template = "image.html.twig"                                    |                                                                                          |
| image_caption     |                                                                                         |                                                                                          |
| image_data        | size = null, data = []                                                                  |                                                                                          |
| image_gallery     | template = "image_gallery.html.twig"                                                    |                                                                                          |
| image_size        |                                                                                         |                                                                                          |
| image_width       |                                                                                         |                                                                                          |
| localized_date    | format = null                                                                           |                                                                                          |
| replace_inserttag | bool cache = true                                                                       | Replace contao inserttag in twig string.                                                 |

### Image Extension

Use the image extension to resize contao images inside your twig template. 

```
{% for box in boxes %}
    {{ box.image|image([0,0,6],{'href' : box.url, 'linkTitle' : 'vmd.content.more.default'|trans})|raw }}
{% endfor %}
```

#### Arguments
- size: array containing width, height or image size config id (`Theme image size id`)
- data: additional image data like css class, linkTitle or href

### Download Extension

Use the download extension to render download elements, get download links, download path or download data.

```
{% set downloadData = singleSRC|download_data %} {#get download data #}
{{ singleSRC|download(true,{'link': 'customLinkTitleHtml'}, '@HeimrichHannotContaoUtils/download.html.twig') {#render as download link and send file to browser link #}
{{ singleSRC|download(false,{'link': 'customLinkTitleHtml'}, '@HeimrichHannotContaoUtils/download.html.twig') {#render as download link and open download in news window #}
{{ singleSRC|download_link) {#get send file to browser download link #}
{{ singleSRC|download_path) {#get download file path #}
{{ singleSRC|download_title) {#get download title for link title attribute e.g. #}
```

## Insert tags

| Insert tag  | Description                                                                                                                                                                                                                                                                                                  |
|-------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| {{twig::*}} | This tag will be replaced with the rendered output of a given twig template, that can be sourced in `bundle/src/Resources/views` or contao root `/templates` directory (replace 1st * with template name e.g. `svg_logo_company` and 2nd * with serialized parameters that should be passed to the template) |

## Commands

**[Entity finder](docs/commands/entity_finder.md)** - A command to search for any contao entities in your database.

**Image size creator** - Creates image size items for a given image size entity.

```
Description:
 Creates image size items for a given image size entity. Image size entities with existing image size items will be skipped.

Usage:
 huh:utils:create-image-size-items [<image-size-ids> [<breakpoints>]]

Arguments:
   image-size-ids        The comma separated ids of the image size. Skip the parameter in order to create image size items for all image size entities.
   breakpoints           The comma separated breakpoints as pixel amounts (defaults to "576,768,992,1200,1400"). [default: "576,768,992,1200,1400"]
   
Example:
   huh:utils:create-image-size-items 1,2
```
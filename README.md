# Contao Utils Bundle

[![](https://img.shields.io/packagist/v/heimrichhannot/contao-utils-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-utils-bundle)
[![](https://img.shields.io/packagist/dt/heimrichhannot/contao-utils-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-utils-bundle)
[![Build Status](https://travis-ci.org/heimrichhannot/contao-utils-bundle.svg?branch=master)](https://travis-ci.org/heimrichhannot/contao-utils-bundle)
[![Coverage Status](https://coveralls.io/repos/github/heimrichhannot/contao-utils-bundle/badge.svg?branch=master)](https://coveralls.io/github/heimrichhannot/contao-utils-bundle?branch=master)

This bundle offers various utility functionality for the Contao CMS.


## Install 

```
composer require heimrichhannot/contao-utils-bundle
```

### Additional Requirements: 

Add following dependencies to your project composer file, if you want to use one of the following utils:

Util                  | Dependency
----------------------|-----------
huh.utils.pdf.writer  | `"mpdf/mpdf": "^7.0"`
huh.utils.pdf.preview | `"spatie/pdf-to-image": "^1.8"` or/and `"alchemy/ghostscript": "^4.1"`


## Usage

This Bundle is a collection of utils to solve recurring tasks. See the [API Documentation](https://heimrichhannot.github.io/contao-utils-bundle/) to see all functions. 
We recommend to call the utils as service. You can either inject them (the Symfony recommend way) or call them from the service container (all util services are public).

Available [Service](src/Resources/config/services.yml) (as of version 2.131):

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
huh.utils.pdf.fpdi_writer
huh.utils.pdf.preview
huh.utils.pdf.writer
huh.utils.pdf_writer
huh.utils.request.curl
huh.utils.routing
huh.utils.salutation
huh.utils.string
huh.utils.template
huh.utils.url
huh.utils.user                                    
```

## Documentation

[API Documentation](https://heimrichhannot.github.io/contao-utils-bundle/)

### Utils 

[PDF Writer](docs/utils/pdf/pdf_writer.md) (`huh.utils.pdf.writer`)

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
 
name | description 
| --- | --- |
| url | The url the request will be send to. |
| data | The data that is used for the request. It has to be passed as JSON. |
| config | An object that can hold the onSuccess-, onError-, beforeSubmit-, afterSubmit-callback and the request headers | 
 
 
Parameters of config

name | description
| --- | --- |
| onSuccess | Is called when the request was successfull. The request is passed as parameter. |
| onError | Is called when the request had an error. The request is passed as parameter. |
| beforeSubmit | Is called before the request is submitted. The url, data and config are passed as parameters. |
| afterSubmit | Is called before the request is submitted. The url, data and config are passed as parameters. |
| headers | The headers will be set when the request is initialized. |
 
The contents of the config parameter are all optional. If you pass an empty config to the ajaxUtil a silent request will be processed.
The data will be transformed in the fitting format accordion wether you use a POST- or a GET-request.

To use the shorthands import the utilsBundle into your script. After that just call the method an pass the needed parameters.

```
import "@hundh/contao-utils-bundle";

...

utilsBundle.ajax.get(url, data, config);
# or
utilsBundle.ajax.post(url, data, config);
```

### Configuration

Following configuration parameter can be overridden:

```yaml
huh_utils:
    tmp_folder: 'files/tmp/huh_utils_bundle'
```

## Twig Extensions

These bundle add server twig filters:

Filter            | Parameter | Description
----------------- | --------- | -----------
autolink          | array options | Create a link if string is an url.
anonymize_email   | - | Returns an anonymized email address. max.muster@example.org will be max.****@example.org
deserialize       | bool force_array = false | Deserialize an serialized array (using `\Contao\StringUtil`)
download          | download = true, data = [], template = "@HeimrichHannotContaoUtils\/download.html.twig" |
download_data     | data = [] |
download_link     | data = [] |
download_path     | data = [] |
download_title    | data = [] |
file_data         | data = [], jsonSerializeOptions = []
file_path         | | 
image             | size = null, data = [], template = "image.html.twig" |
image_caption     | | 
image_data        | size = null, data = [] |
image_gallery     | template = "image_gallery.html.twig"
image_size        | |
image_width       | |
localized_date    | format = null |
replace_inserttag | bool cache = true | Replace contao inserttag in twig string.


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

| Insert tag  | Description  |
|---|---|
| {{twig::*}}  | This tag will be replaced with the rendered output of a given twig template, that can be sourced in `bundle/src/Resources/views` or contao root `/templates` directory (replace 1st * with template name e.g. `svg_logo_company` and 2nd * with serialized parameters that should be passed to the template) |
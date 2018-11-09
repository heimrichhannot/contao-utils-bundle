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

Available [Service](src/Resources/config/services.yml) (as of version 2.25):

```php
huh.utils.array
huh.utils.cache.database
huh.utils.cache.remote_image_cache
huh.utils.cache.file
huh.utils.code
huh.utils.encryption
huh.utils.container
huh.utils.database
huh.utils.date
huh.utils.dca
huh.utils.file
huh.utils.form
huh.utils.image
huh.utils.location
huh.utils.model
huh.utils.request.curl
huh.utils.string
huh.utils.url
huh.utils.choice.field
huh.utils.choice.data_container
huh.utils.choice.message
huh.utils.choice.model_instance
huh.utils.choice.twig_template
huh.utils.routing
huh.utils.salutation
huh.utils.class
huh.utils.member
huh.utils.template
huh.utils.user
huh.utils.pdf.preview
huh.utils.pdf.writer
```

## Documentation

[API Documentation](https://heimrichhannot.github.io/contao-utils-bundle/)

### Utils 

[PDF Writer](docs/utils/pdf/pdf_writer.md) (`huh.utils.pdf.writer`)

### Using with Webpack/Encore

The bundle assets (js) are prepared to be used with webpack/encore. If you don't use [Foxy](https://github.com/fxpio/foxy), you need to add `"contao-utils-bundle": "^1.0.0"` to your project package.json. 

Usage example:

```
import(/* webpackChunkName: "contao-utils-bundle" */ 'contao-utils-bundle').then(
UtilsBundle => {
    if (UtilsBundle.util.isTruthy(value))) {
        // js code
    }
});
```

You'll find the package source [here](https://github.com/heimrichhannot-contao-components/contao-utils-bundle).

## Twig Extensions

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

## Insert tags

| Insert tag  | Description  |
|---|---|
| {{twig::*}}  | This tag will be replaced with the rendered output of a given twig template, that can be sourced in `bundle/src/Resources/views` or contao root `/templates` directory (replace 1st * with template name e.g. `svg_logo_company` and 2nd * with serialized parameters that should be passed to the template) |

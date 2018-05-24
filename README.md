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
huh.utils.pdf.preview | `"spatie/pdf-to-image": "^1.8"`


## Usage

This Bundle is a collection of utils to solve recurring tasks. See the [Documentation](https://heimrichhannot.github.io/contao-utils-bundle/) to see all functions. 
We recommend to call the utils as service. You can either inject them (the Symfony recommend way) or call them from the service container (all util services are public).

Available [Service](src/Resources/config/services.yml) (as of version 2.14):

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
huh.utils.class
huh.utils.member
huh.utils.template
huh.utils.user
huh.utils.pdf.preview
huh.utils.pdf.writer
```

## Documentation

[Documentation](https://heimrichhannot.github.io/contao-utils-bundle/)

#### Additional information

[PDF Writer](docs/utils/pdf/pdf_writer.md) (`huh.utils.pdf.writer`)

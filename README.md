# Contao Utils Bundle

[![](https://img.shields.io/packagist/v/heimrichhannot/contao-utils-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-utils-bundle)
[![](https://img.shields.io/packagist/dt/heimrichhannot/contao-utils-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-utils-bundle)
[![](https://img.shields.io/travis/heimrichhannot/contao-utils-bundle/master.svg)](https://travis-ci.org/heimrichhannot/contao-utils-bundle/)
[![](https://img.shields.io/coveralls/heimrichhannot/contao-utils-bundle/master.svg)](https://coveralls.io/github/heimrichhannot/contao-utils-bundle)

This bundle offers various utility functionality for the Contao CMS.


## Install 

```
composer require heimrichhannot/contao-utils-bundle
```


## Usage

All utils are public symfony services and can be called from the service container or be injected.

Available [Service](src/Resources/config/services.yml) (as at version 1.0.4):

* `huh.utils.array`
* `huh.utils.cache.remote_image_cache`
* `huh.utils.choice.data_container`
* `huh.utils.choice.field`
* `huh.utils.choice.message`
* `huh.utils.choice.model_instance`
* `huh.utils.code`
* `huh.utils.container`
* `huh.utils.database`
* `huh.utils.date`
* `huh.utils.dca`
* `huh.utils.encryption`
* `huh.utils.file`
* `huh.utils.form`
* `huh.utils.image`
* `huh.utils.model`
* `huh.utils.request.curl`
* `huh.utils.routing`
* `huh.utils.string`
* `huh.utils.url`


## Documentation

You'll find all available util methods in the [API Documentation](https://heimrichhannot.github.io/contao-utils-bundle/).
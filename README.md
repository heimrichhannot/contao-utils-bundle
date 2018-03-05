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

All Utils can are public symfony services and can be called from the service container or be injected.

Services are named by the pattern `huh_utils_[optional:namesspace_][utils class]` (in a good IDE, just type `huh_utils` and you will see all options).

Examples:
* `huh.utils.cache.remote_image_cache`
* `huh.utils.curl`
* `huh.utils.dca`
* `huh.utils.model`
* `huh.utils.routing`

[API Documentation](https://heimrichhannot.github.io/contao-utils-bundle/)
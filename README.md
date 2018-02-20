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


## Utils

### Cache

#### Remote image cache `huh.utils.cache.remote_image_cache`

Method     | Description
-----------|------------
get        | Get a remote file from cache and cache file, if not already in cache.

### Curl `huh.utils.curl`

Method                     | Description
---------------------------|------------
recursivelyGetRequest      |
request                    | 
recursivelyPostRequest     |
postRequest                |
createCurlObject           |
setHeaders                 |
splitResponseHeaderAndBody |
prepareHeaderArrayForPrint |


### DCA `huh.utils.dca`

Method                               | Description
-------------------------------------|------------
getConfigByArrayOrCallbackOrFunction |
setDateAdded                         |
setDateAddedOnCopy                   |
getFields                            |
addOverridableFields                 |
getOverridableProperty               |
flattenPaletteForSubEntities         | 
generateAlias                        | Generate a unique alias or check if given alias is unique

### Models `huh.utils.model`

Method                    | Description
--------------------------|------------
findModelInstanceByPk     | Returns a model instance if for a given table and id or null if not exist.
findModelInstancesBy      | Returns model instances by given table and search criteria. 
findOneModelInstanceBy    | Return a single model instance by table and search criteria.
findRootParentRecursively | Recursively finds the root parent.
findParentsRecursively    |Returns an array of a model instance's parents in ascending order, i.e. the root parent comes first.

### Routing `huh.utils.routing`

Method               | Description
---------------------|------------
generateBackendRoute | Generate a backend Route with request token and referer.
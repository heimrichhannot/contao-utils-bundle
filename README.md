# Contao Utils Bundle

[![](https://img.shields.io/packagist/v/heimrichhannot/contao-utils-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-utils-bundle)
[![](https://img.shields.io/packagist/dt/heimrichhannot/contao-utils-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-utils-bundle)
[![](https://img.shields.io/travis/heimrichhannot/contao-utils-bundle/master.svg)](https://travis-ci.org/heimrichhannot/contao-utils-bundle/)
[![](https://img.shields.io/coveralls/heimrichhannot/contao-utils-bundle/master.svg)](https://coveralls.io/github/heimrichhannot/contao-utils-bundle)

This bundle offers various utility functionality for the Contao CMS.

## Utils

### Models `huh.utils.model`

Method                | Description
----------------------|------------
findModelInstanceByPk | Returns a model instance if for a given table and id or null if not exist.
findModelInstancesBy  | Returns model instances by given table and search criteria. 
findOneModelInstanceBy | Return a single model instance by table and search criteria.
findRootParentRecursively | Recursively finds the root parent.
findParentsRecursively |Returns an array of a model instance's parents in ascending order, i.e. the root parent comes first.

### Routing `huh.utils.routing`

Method               | Description
---------------------|------------
generateBackendRoute | Generate a backend Route with request token and referer.
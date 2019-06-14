# Contao Utils Bundle Assets

This package contains the frontend assets of the composer bundle [heimrichhannot/contao-utils-bundle](https://github.com/heimrichhannot/contao-utils-bundle).

## Setup

### Install

`yarn add @hundh/contao-utils-bundle`

### Usage

#### Webpack/Encore

Usage example:

```
import { DomUtil, ArrayUtil } from '@hundh/contao-utils-bundle';

DomUtil.scrollTo(myNode, 100);
```

Following imports possible:
* ArrayUtil
* DomUtil
* EventUtil
* GeneralUtil
* UrlUtil
* UtilsBundle

`UtilsBundle` holds all utilities classes:
* array
* dom
* event
* url
* util

Example usage: `UtilsBundle.util.isTruthy(value)`

#### Legacy libraries

If you run the package code at least once, the `UtilsBundle` object will be written to `window.utilsBundle;`.

Usage example:
```
let UtilsBundle = window.utilsBundle;
```






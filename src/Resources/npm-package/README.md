# Contao Utils Bundle Assets

This package contains the frontend assets of the composer bundle [heimrichhannot/contao-utils-bundle](https://github.com/heimrichhannot/contao-utils-bundle).

## Install

`yarn add @hundh/contao-utils-bundle`

## Usage

### Ajax Util

```js
import AjaxUtil from "@hundh/contao-utils-bundle/js/ajax-util";

let config = {
    headers: {'X-Requested-With': 'XMLHttpRequest'},
    responseType: undefined, // set XMLHttpRequest.responseType
    onSuccess: undefined, //on success callback
    onError: undefined, // on error callback
    beforeSubmit: undefined, //before submit callback
    afterSubmit: undefined // after submit callback
};

/**
* @var {string} url
* @var {FormData|object} data
* @var {Object} config
*/
AjaxUtil.get(url, data, config);
AjaxUtil.post(url, data, config);
```

### Webpack/Encore

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

### Legacy libraries

If you run the package code at least once, the `UtilsBundle` object will be written to `window.utilsBundle;`.

Usage example:
```
let UtilsBundle = window.utilsBundle;
```






# Changelog
All notable changes to this project will be documented in this file.

## [1.0.5] - 2018-03-02

### Fixed
- use `serialize($context)` instead of `json_encode($context)` to get unique `cacheKey` including php objects within `AbstractChoice`

## [1.0.4] - 2018-03-02

### Changed
- reference `$GLOBALS['TL_LANG']['MSC']['operators']` to `$GLOBALS['TL_LANG']['MSC']['databaseOperators']`

## [1.0.3] - 2018-03-02

### Fixed
- Image to ImageUtil

## [1.0.2] - 2018-03-01

### Fixed
- default table prefixing in DatabaseUtil::computeCondition()

## [1.0.1] - 2018-02-28

### Fixed
- unit tests
- randomly encryption error using openssl (see: https://stackoverflow.com/questions/37439981/openssl-encrypt-randomly-fails-iv-passed-is-only-x-bytes-long-cipher-exp?answertab=votes#tab-top)
- removed container argument from `RemoteImageCache` constructor

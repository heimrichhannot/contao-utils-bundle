# Changelog
All notable changes to this project will be documented in this file.

## [2.0.7] - 2018-03-14

### added
-  \DateTime input support for `DateUtil::getTimeStamp()`

## [2.0.6] - 2018-03-14

### added
-  `DateUtil::getTimeStamp()` that converts any input date format to a timestamp 

## [2.0.5] - 2018-03-14

### fixed
-  `AbstractChoice` debug check

## [2.0.4] - 2018-03-13

### fixed
-  fixed service name

## [2.0.3] - 2018-03-13

### added
-  added ClassUtil service

### fixed
-  fixed ClassUtil namespace

## [2.0.2] - 2018-03-13

### added
-  added ClassUtil class with test class

## [2.0.1] - 2018-03-12

### fixed
-  fixed travis.yml

## [2.0.0] - 2018-03-12

### Changed
-  replaced `heimrichhannot/contao-request` with `heimrichhannot/contao-requets-bundle`

## [1.1.5] - 2018-03-09

### Fixed
-  travis build and composer dependencies

## [1.1.4] - 2018-03-09

### Fixed
-  travis build and composer dependencies

## [1.1.3] - 2018-03-08

### Changed
- `DatabaseUtil::computeCondition` values now replaces inserttags

## [1.1.2] - 2018-03-08

### Changed
- added `DatabaseUtil::computeCondition` remove table name from field name if already set mistakenly

## [1.1.1] - 2018-03-07

### Fixed
- added `DC_Table_Utils` missing `isset()` checks

### Changed
- `travis.yml` and `composer.json` test dependencies

## [1.1.0] - 2018-03-07

### Added
- TextualPagination

## [1.0.5] - 2018-03-07

### Added
- added getDataContainers function

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

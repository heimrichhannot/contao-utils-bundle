# Changelog
All notable changes to this project will be documented in this file.

## [2.3.1] - 2018-04-04

### Added
- js deps to encore yml
- LocationUtil::computeCoordinatesByArray()
- LocationUtil::computeCoordinatesByString()

## [2.3.0] - 2018-03-28

### Added
- ArrayUtil::removePrefix()
- DatabsseCacheUtil
- StringUtil::removeLeadingString()
- LocationUtil

## [2.2.2] - 2018-03-27

### Added
- `lazyload` to `picture.html.twig` template

## [2.2.1] - 2018-03-27

### Added
- `addImageToTemplateData` HOOK to add custom template data within `huh.utils.image` method `addToTemplateData()`

## [2.2.0] - 2018-03-23

### Added
- javascript array, util

## [2.1.8] - 2018-03-22

### Fixed
- `ClassUtil::jsonSerialize` now supports `is`, `has` and `get` function getters but wont overwrite if a sibling also exists (keep is, has, get prefix than)

## [2.1.7] - 2018-03-22

### Fixed
- `huh.utils.choice.twig_template` returned template path name should not contain colon, fixed bundle key by removing last `Bundle` occurrence in name

## [2.1.6] - 2018-03-22

### Fixed
- `huh.utils.choice.twig_template` prefix filter

## [2.1.5] - 2018-03-22

### Fixed
- unit tests

## [2.1.4] - 2018-03-22

### Added
- `huh.utils.choice.twig_template` that will return all available templates or filter out templates by given prefixes 

## [2.1.3] - 2018-03-21

### Added
- support for upcoming heimrichhannot/contao-encore-bundle

## [2.1.2] - 2018-03-21

### Added
- StringUtil::removeLeadingAndTrailingSlash()

## [2.1.1] - 2018-03-20

### Fixed
- added missing pagination message keys

## [2.1.0] - 2018-03-20

### Added
- javascript url utility

### Fixed
- ContainerUtil::mergeConfigFile to be more forgiving

## [2.0.16] - 2018-03-19

### Changed
- `ClassUtil::jsonSerialize` class name removed from argument list

## [2.0.15] - 2018-03-19

### Fixed
- add missing `isset()` to  `ModelInstanceChoice::collect()`

## [2.0.14] - 2018-03-19

### fixed
- fixed sanitizeFileName

## [2.0.13] - 2018-03-19

### Added
-added getPixelValue function to ImageUtil

## [2.0.12] - 2018-03-19

### Added
-added pregReplaceLast function to StringUtil

## [2.0.11] - 2018-03-15

### Fixed
- changelog

## [2.0.10] - 2018-03-15

### Fixed
- textual pagination

## [2.0.9] - 2018-03-14

### Changed
- enhanced ClassUtil::jsonSerialize()

## [2.0.8] - 2018-03-14

### Added
- ClassUtil::jsonSerialize()

## [2.0.7] - 2018-03-14

### Added
- \DateTime input support for `DateUtil::getTimeStamp()`

## [2.0.6] - 2018-03-14

### Added
- `DateUtil::getTimeStamp()` that converts any input date format to a timestamp 

## [2.0.5] - 2018-03-14

### Fixed
- `AbstractChoice` debug check

## [2.0.4] - 2018-03-13

### Fixed
- fixed service name

## [2.0.3] - 2018-03-13

### Added
- added ClassUtil service

### Fixed
- fixed ClassUtil namespace

## [2.0.2] - 2018-03-13

### Added
- added ClassUtil class with test class

## [2.0.1] - 2018-03-12

### Fixed
- fixed travis.yml

## [2.0.0] - 2018-03-12

### Changed
- replaced `heimrichhannot/contao-request` with `heimrichhannot/contao-requets-bundle`

## [1.1.5] - 2018-03-09

### Fixed
- travis build and composer dependencies

## [1.1.4] - 2018-03-09

### Fixed
- travis build and composer dependencies

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

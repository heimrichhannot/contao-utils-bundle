# Changelog
All notable changes to this project will be documented in this file.

## [2.18.0] - 2018-06-14

#### Added
- dc_multilingual features to ModelUtil

## [2.17.0] - 2018-06-12

#### Added
- ModelUtil::findMultipleModelInstancesByIds(), ModelUtil::findModelInstanceByIdOrAlias()
- support in ModelUtil for models inheriting from DC_Multilingual

## [2.16.2] - 2018-06-08

#### Fixed
- CodeUtil::generate() -> missing "use" of PWGen

## [2.16.1] - 2018-06-07

#### Fixed
- `huh.utils.dca` method `addOverridableFields()`, added missing array check for  `__selector__`  palette

## [2.16.0] - 2018-06-07

#### Added
- `huh.utils.model` method `hasValueChanged()`
- `huh.utils.model` method `getModelInstanceFieldValue()`

## [2.15.2] - 2018-06-04

#### Added
- DatabaseUtil::getChildRecords() (including recursive retrieval)

## [2.15.1] - 2018-06-04

#### Fixed
- TemplateUtil::removeTemplateComment

## [2.15.0] - 2018-05-29

#### Added 
- option to choose pdf transcoder for PdfPreview
- added alchemy/ghostscript as option for PdfPreview

#### Fixed
- filename for save callback in FileCache had no file extension


## [2.14.1] - 2018-05-24

#### Added
- utils js to backend

## [2.14.0] - 2018-05-24

#### Added
- FileCache util
- PdfPreview util
- renamed pdf writer util (keeped old service name as alias and marked deprecated)

## [2.13.0] - 2018-05-16

#### Added 
- TemplateUtil::removeTemplateComment()

## [2.12.2] - 2018-05-16

#### Fixed
* picture template twig error if title not defined
* enhanced code documentation

## [2.12.1] - 2018-05-11

### Added 
- fixed computeCondition

## [2.12.0] - 2018-05-09

### Added 
- `huh.utils.model` method `findAllModelInstances` to get all models from a table

## [2.11.0] - 2018-05-09

### Added
- `huh.utils.dca` method `getEditLink()` to create an contao backend edit link (e.g. module)
- `huh.utils.dca` method `getModalEditLink()` to create an contao backend modal edit link (e.g. module)
- `huh.utils.dca` method `getArchiveModalEditLink()` to create an contao backend archive modal edit link (e.g. module)

## [2.10.2] - 2018-05-08

### Fixed 
- removed whitespace from composeWhereForQueryBuilder in operator

## [2.10.1] - 2018-05-08

### Fixed 
- `huh.utils.form` call `Widget::getAttributesFromDca()` in `getWidgetFromAttributes` non-static

## [2.10.0] - 2018-05-04

### Added 
- `huh.utils.form` method `getWidgetFromAttributes`

## [2.9.1] - 2018-05-04

### Fixed
- fixed slow template loading by adding Template caching

## [2.9.0] - 2018-05-04

### Added
- `huh.utils.pdf_writer` service

## [2.8.8] - 2018-04-26

### Fixed
- convert array values to string values in `DatabaseUtil::computeCondition` and `DataBaseUtil::composeWhereForQueryBuilder` to prevent `Controller::replaceInsertTags()` exception

## [2.8.7] - 2018-04-26

### Fixed
- `TemplateUtil::getTemplate` now catch template not found exception and try to find the twig templates inside bundle views directory 

## [2.8.6] - 2018-04-26

### Fixed
- `TemplateUtil::getTemplate` now also returns the path for twig templates inside bundle views directory 

## [2.8.5] - 2018-04-26

### Fixed
- `ClassUtil::jsonSerialize` now priorities `getMethod` before `hasMethod` and `isMethod`

## [2.8.4] - 2018-04-26

### Fixed
- `picture.html.twig` lazyload padding, number format (use dot as decimal point instead of comma)

## [2.8.3] - 2018-04-25

### Fixed
- `TemplateUtil::getTemplateGroup` fixed for bundles/modules `/templates` files 

## [2.8.2] - 2018-04-25

### Fixed
- composer dependencies 

## [2.8.1] - 2018-04-25

### Fixed
- `TwigTemplateChoice` bundles is array check added 

## [2.8.0] - 2018-04-25

### Added
- User::getActiveByGroups
- ArrayUtil::insertInArrayByName
- ArrayUtil::arrayToObject
- DcaUtil::addAliasToDca
- DcaUtil::getLocalizedFieldName
- ModelUtil::getModelInstanceIfId
- StringUtil::html2Text
- StringUtil::lowerCase
- StringUtil::convertToInlineCss
- UserUtil::findActiveByGroups
- UserUtil::hasAccessToField
- added composer dependencies

## [2.7.5] - 2018-04-25

### Fixed
- modelUtil::findInstancesBy

## [2.7.4] - 2018-04-25

### Fixed
- `TemplateUtil:getTemplateGroup` for `templates` directory in bundle/modules 

## [2.7.3] - 2018-04-25

### Fixed
- `TemplateUtil:findTemplates` now supports regex lookaheads in pattern 

## [2.7.2] - 2018-04-25

### Fixed
- `TwigTemplateChoice` twig template pattern 

## [2.7.1] - 2018-04-25

### Changed
- `TwigTemplateChoice` now set template basename as choice key 

## [2.7.0] - 2018-04-25

### Added
- `TemplateUtil::getTemplateGroup` to provide `twig.html` support within contao 

### Changed
- `TwigTemplateChoice` now returns all `.html.twig` template inside any contao `templates` directory, like default contao `.html5` handling

## [2.6.0] - 2018-04-24

### Added
- `TemplateUtil::getTemplate` to provide `twig.html` support within contao 
- `ModelUtil::setDefaultsFromDca()` and `DcaUtil::setDefaultsFromDca()` 

## [2.5.9] - 2018-04-20

### Changed
- `DatabaseUtil::composeWhereForQueryBuilder` now converts values using  `Controller::replaceInsertTags()`

## [2.5.8] - 2018-04-20

### Changed
- visibility of CurlRequest::getCurlHandle() to public

## [2.5.7] - 2018-04-20

### Fixed
- `setContext` and `getContext` handling in `AbstractChoice`

### Changed
- `TwigTemplateChoice` now also collects twig templates inside root `templates/` directory and returns the path to the template e.g. `templates/news_full.html.twig`

## [2.5.6] - 2018-04-18

### Added
- added member service and MemberUtil::findActiveByGroups

## [2.5.5] - 2018-04-17

### Added
- new option for DateUtil::getFields()

## [2.5.4] - 2018-04-12

### Fixed
- DatabaseUtil computeCondition in and notin operator

## [2.5.3] - 2018-04-12

### Fixed
- DatabaseUtil composeWhereForQueryBuilder in and notin operator

## [2.5.2] - 2018-04-10

### Added
- DateUtil::transformPhpDateFormatToISO8601()

## [2.5.1] - 2018-04-10

### Added
- StringUtil::removeTrailingString()

### Fixed
- bugs

## [2.5.0] - 2018-04-06

### Added
- ModelUtil::computeStringPattern()

## [2.4.0] - 2018-04-05

### Added
- MemberUtil

## [2.3.3] - 2018-04-05

### Fixed
- `DC_Table_Utils` did not trigger `Controller::loadDataContainer($strTable)` before `isset($GLOBALS['TL_DCA'][$strTable])` check

## [2.3.2] - 2018-04-05

### Added
- optional yarn package for frontend assets

### Fixed
- field choice and dcaUtil->getFields

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

# Changelog
All notable changes to this project will be documented in this file.

## [2.53.0] - 2018-12-11

### Added
- css classes from img-class `contao picture sizes` added to `figure` element with prefix `figure-`

## [2.52.0] - 2018-12-07

### Added
- ClassUtil::callInaccessibleMethod()

## [2.51.1] - 2018-12-06

### Fixed
- strict comparison issues

## [2.51.0] - 2018-12-06

### Added
- DcaUtil::generateSitemap()

## [2.50.0] - 2018-12-04

### Added
- `image_width` and `image_caption` twig filters

## [2.49.3] - 2018-11-30

### Fixed
- Added missing twig template for `DownloadExtension`

## [2.49.2] - 2018-11-30

### Fixed
- DateUtil

## [2.49.1] - 2018-11-30

### Fixed
- replace inserttags in Twig `DownloadExtension`

## [2.49.0] - 2018-11-30

### Fixed
- urldecode file path in Twig `DownloadExtension` to support 

### Added
- Twig Plugin `DownloadExtension` function `download_title`

## [2.48.0] - 2018-11-30

### Added
- Twig Plugin `DownloadExtension` with `download`, `download_link`, `download_path`, `download_data`

## [2.47.1] - 2018-11-29

### Fixed
- `huh.util.image` addToTemplateData() now returns if image from uuid was not found

## [2.47.0] - 2018-11-29

### Added
- DateExtension

## [2.46.2] - 2018-11-28

### Fixed
- restore img css class in `picture.html.twig` while lazyload is active

## [2.46.1] - 2018-11-26

### Fixed
- DateUtil::getShortMonthTranslationMap(), DateUtil::getMonthTranslationMap()

## [2.46.0] - 2018-11-26

### Changed
- made support for properties in ClassUtil::jsonSerialize() optional

## [2.45.0] - 2018-11-26

### Added
- support for properties in ClassUtil::jsonSerialize()

## [2.44.2] - 2018-11-19

### Fixed
- contao 4.6.8 uses contao.csrf.token_manager service to validate token

## [2.44.1] - 2018-11-19

### Fixed
- symfony 4.x and contao 4.6+ support

## [2.44.0] - 2018-11-15

### Added
- TemplateUtil::getTemplateGroup now also search within bundle views folders

## [2.43.0] - 2018-11-14

### Added
- ModelUtil -> Controller::replaceInsertTags for values

## [2.42.2] - 2018-11-14

### Fixed
- ClassUtil json serialization

## [2.42.0] - 2018-11-14

### Added
- new methods to DateUtil: isMonthInDateFormat(), isDayInDateFormat(), isYearInDateFormat(), getMonthTranslationMap(), getShortMonthTranslationMap(), translateMonthsToEnglish(), translateMonths()

## [2.41.0] - 2018-11-14

### Added
- static `ArrayUtil::insertBeforeKey` to modify execution order in hooks and callbacks

## [2.40.1] - 2018-11-13

### Fixed
- `huh.utils.template` do not cache non existing templates within `getTemplate()`

## [2.40.0] - 2018-11-12

### Added
- italian translation, thanks to `MicioMax`

## [2.39.0] - 2018-11-08

### Added
- DcaUtil::generateDcOperationsButtons()

## [2.38.0] - 2018-11-08

### Added
- `image` twig extension

### Fixed
- small bugs in `huh.utils.image` addToTemplateData() function

## [2.37.0] - 2018-11-08

### Added
- `target="_blank"` to `image.html.twig` template if `target` variable is true

## [2.36.0] - 2018-11-08

### Added
- DcaUtil::isDcMultilingual & DcaUtil::loadDc

### Fixed
- dc_multilingual support in ModelUtil

## [2.35.0] - 2018-11-08

### Added
- `huh.utils.image` addToTemplateData() now supports also `uuid` instead of `file path` value

## [2.34.3] - 2018-11-07

### Fixed
- AccordionUtil

## [2.34.2] - 2018-11-07

### Fixed
- dc_multilingual support in ModelUtil

## [2.34.1] - 2018-11-07

### Fixed
- linkTitle issue in `image.html.twig`

## [2.34.0] - 2018-11-06

### Added
- `huh.utils.url` method `isNewVisitor()` to detect if user referer and current host with scheme match

## [2.33.0] - 2018-11-05

### Added
- AccordionUtil

## [2.32.0] - 2018-11-05

### Added
- TemplateUtil::renderTwigTemplate()

## [2.31.0] - 2018-11-02

### Added
- TemplateUtil::getPageAliasAsCssClass()

## [2.30.4] - 2018-11-01

### Fixed
- TemplateUtil::getBundleTemplate -> did not respect the bundle order

## [2.30.2] - 2018-10-22

### Fixed
- `huh.utils.listener.insert_tags` was not public

## [2.30.1] - 2018-10-12

### Fixed
- surround `$file->imageSize` in `ImageUtil::addToTemplateData` with catch block, to prevent error messages for non existing images to stop working site

## [2.30.0] - 2018-09-25

### Added
- Inserttag `twig` (Example:`{{twig::logo.html.twig::a:1:{s:3:"foo";s:3:"bar";}}}`) to render twig templates from inserttags with custom serialized data

## [2.29.2] - 2018-09-18

### Changed
- code style

## [2.29.1] - 2018-09-18

### Changed
- code style

## [2.29.0] - 2018-09-10

### Changed
- removed `"symfony/framework-bundle": "^3.4"` composer dependency to restore symfony 4 and contao 4.6 compability
- removed reprecated tag from deprecated `huh.utils.pdf_writer` service to restore symfony 4 compability
- updated model namespace in `CfgTagModel`

## [2.28.10] - 2018-09-07

### Fixed

- Server error 500 while trying to warmup cache due to `Uncaught Error: Call to undefined method Contao\\ManagerBundle\\HttpKernel\\ContaoCache::getProjectDir() ` while invoking `config_encore.yml`

## [2.28.9] - 2018-08-28

### Fixed
- DcaUtil::addAliasToDca()

## [2.28.8] - 2018-08-23

### Added
- dom.js

## [2.28.7] - 2018-08-21

### Added
- CurlRequestUtil::HTTP_STATUS_CODE_MESSAGES

## [2.28.6] - 2018-08-20

### Fixed
- mode issue in FormUtil::getWidgetFromAttributes()

## [2.28.5] - 2018-08-16

#### Changed
- use `translator.default` service instead of `translator` service due changes in contao 4.5

## [2.28.4] - 2018-08-14

#### Fixed
- Contao 4.5 compability in MessageChoice
- some deprecation warnings

#### Update
- small code enhancements

## [2.28.3] - 2018-08-14

#### Fixed
- missing parameter for template util service

## [2.28.2] - 2018-08-14

#### Fixed
- namespace in `RoutingUtil`

## [2.28.1] - 2018-08-14

#### Fixed
- error when null as parameter in TemplateUtil::isTemplatePartEmpty

## [2.28.0] - 2018-08-13

#### Added
- TemplateUtil::isTemplatePartEmpty

#### Changed
- update some documentation comments
- small code enhancements

## [2.27.0] - 2018-08-06

### Fixed
- tag issue

## [2.25.5] - 2018-08-06

### Fixed
- mode issue in FormUtil::getWidgetFromAttributes()

## [2.25.4] - 2018-08-06

### Fixed
- login=1 error in UserUtil

## [2.25.3] - 2018-07-31

### Fixed
- DcaUtil::getConfigByArrayOrCallbackOrFunction() not processing service callbacks
- DcaUtil::getModalEditLink() returns contao 3 backend route
- DcaUtil::getEditLink() returns contao 3 backend route
- DcaUtil::getArchiveModalEditLink() returns contao 3 backend route
- deprecation warning with using non-public contao.routing.scope_matcher service in ContainerUtil
- updated some contao namespaces

## [2.25.2] - 2018-07-27

### Changed
- readme
- tests

## [2.25.1] - 2018-07-27

### Changed
- updated readme

## [2.25.0] - 2018-07-17

### Added
- `ArrayUtil::flattenArray`

### Changed
- enhanced documentation

### Fixed
- `ArrayUtil::getArrayRowByFieldValue` was static

## [2.24.1] - 2018-07-20

### Fixed
- prepend `{{env::url}}/` to  `data-srcset`, `data-src`, `data-lazy` attribute in `picture_default.html.twig` template in order to load images via absolute url, otherwise images might get loaded from `page-alias/assets/images` (iOS) and trigger 404 error which will result in too many http requests (may slow down server in huge way)

## [2.24.0] - 2018-07-17

### Added
- `ArrayUtil::getArrayRowByFieldValue`

## [2.23.1] - 2018-07-11

### Fixed
- lazyloading not respected source elements in picture tags in `picture.html.twig`

> Caution: May lead to broken css styles due moving image-wrapper element out of picture element.

## [2.23.0] - 2018-07-11

### Added
- `huh.utils.url` method `addURIScheme` to add an protocol to a given url (default: `http`)

## [2.22.4] - 2018-07-11

### Fixed
- `AbstractChoice` cacheKey should only replace last `Choice` occurence in name to maintain unique cache key

## [2.22.3] - 2018-06-25

### Fixed
- `huh.utils.class` method `jsonSerialize` should made usage of `getNumberOfRequiredParameters` instead of `count($rm->getParameters())`

## [2.22.2] - 2018-06-26

### Fixed
- DatabaseUtil::composeWhereForQueryBuilder, DatabaseUtil::composeWhereForQueryBuilder if value for IN is empty break

## [2.22.1] - 2018-06-25

### Fixed
- ContainerUtil::isBundleActive() -> now also contao 3 module names possible

## [2.22.0] - 2018-06-22

### Added
- SalutationUtil

## [2.21.0] - 2018-06-21

### Added
- `DatabaseUtil::OPERATOR_IS_EMPTY` and `DatabaseUtil::OPERATOR_IS_NOT_EMPTY` to `huh.utils.database`

## [2.20.1] - 2018-06-20

### Added
- api key support for locationUtil service

## [2.20.0] - 2018-06-19

### Added
- ModelUtil::findModulePages()

## [2.19.0] - 2018-06-15

### Added
- ContainerUtil::getBundlePath()
- ContainerUtil::getBundleResourcePath()

## [2.18.4] - 2018-06-15

### Fixed
- `huh.utils.template`  method `getTemplate` does also return a template within bundles views directory for core templates that does not end with `html5` like `event_full.html.twig`

## [2.18.3] - 2018-06-14

### Fixed
- ModelUtil::findModelInstanceByIdOrAlias()

## [2.18.2] - 2018-06-14

### Fixed
- ModelUtil database error at compile time

## [2.18.1] - 2018-06-14

### Added
- DateUtil::getGMTMidnightTstamp()

## [2.18.0] - 2018-06-14

### Added
- dc_multilingual features to ModelUtil

## [2.17.0] - 2018-06-12

### Added
- ModelUtil::findMultipleModelInstancesByIds(), ModelUtil::findModelInstanceByIdOrAlias()
- support in ModelUtil for models inheriting from DC_Multilingual

## [2.16.2] - 2018-06-08

### Fixed
- CodeUtil::generate() -> missing "use" of PWGen

## [2.16.1] - 2018-06-07

### Fixed
- `huh.utils.dca` method `addOverridableFields()`, added missing array check for  `__selector__`  palette

## [2.16.0] - 2018-06-07

### Added
- `huh.utils.model` method `hasValueChanged()`
- `huh.utils.model` method `getModelInstanceFieldValue()`

## [2.15.2] - 2018-06-04

### Added
- DatabaseUtil::getChildRecords() (including recursive retrieval)

## [2.15.1] - 2018-06-04

### Fixed
- TemplateUtil::removeTemplateComment

## [2.15.0] - 2018-05-29

### Added 
- option to choose pdf transcoder for PdfPreview
- added alchemy/ghostscript as option for PdfPreview

### Fixed
- filename for save callback in FileCache had no file extension


## [2.14.1] - 2018-05-24

### Added
- utils js to backend

## [2.14.0] - 2018-05-24

### Added
- FileCache util
- PdfPreview util
- renamed pdf writer util (keeped old service name as alias and marked deprecated)

## [2.13.0] - 2018-05-16

### Added 
- TemplateUtil::removeTemplateComment()

## [2.12.2] - 2018-05-16

### Fixed
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

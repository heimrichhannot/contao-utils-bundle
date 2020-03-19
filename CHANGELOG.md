# Changelog
All notable changes to this project will be documented in this file.

## [2.150.0] - 2020-03-19
- added `UserUtil::isAdmin()`

## [2.149.0] - 2020-03-17
- added `FileUtil::getParentFoldersByUuid()`

## [2.148.1] - 2020-03-12
- updated documentation

## [2.148.0] - 2020-03-12
- added FileStorageUtil as replacement for FileCache
- deprecated FileCache
- replaced FileCache with FileStorageUtil in PdfPreview
- fixed PdfPreview not working when destination folder does not exist2.148.

## [2.147.0] - 2020-03-10

- fixed nested record style

## [2.146.0] - 2020-03-10

- added `DcaExtension`
- added `DcaExtension::fieldLabel`

## [2.145.0] - 2020-03-09

- added `DatabaseUtil::insert()`
- added `DatabaseUtil::update()`
- added `DatabaseUtil::delete()`

## [2.144.0] - 2020-03-06

- added `DcaUtil::prepareRowEntryForList()`
- added `DcaUtil::getFieldLabel()`

## [2.143.0] - 2020-03-05

- added `DcaUtil::getRenderedDiff()`
- added `ArrayUtil::implodeRecursive()`

## [2.142.0] - 2020-03-05

- added `DcaUtil::setFieldsToReadOnly()`
- added `DcaUtil::getTranslatedModuleNameByTable()`

## [2.141.1] - 2020-03-03

- added option `restrictFields` to `FormUtil::getModelDataAsNotificationTokens()`

## [2.141.0] - 2020-03-03

- added DateUtil::getDaysBetween()
- fixed UrlUtil::getBaseUrl()

## [2.140.0] - 2020-03-02

- fixed service definitions for contao 4.9

## [2.139.0] - 2020-02-27

- added UrlUtil::getBaseUrl()

## [2.138.0] - 2020-02-27

- added FormUtil::getModelDataAsNotificationTokens()

## [2.137.0] - 2020-02-26

- added DcaUtil::activateNotificationType()

## [2.136.1] - 2020-02-26

- fixed adding formData to uri
- updated dependencies

## [2.136.0] - 2020-02-26

- added DcaUtil::getAuthorNameLinkByUserId and DcaUtil::getAuthorNameByUserId

## [2.135.2] - 2020-02-26

- fixed DcaUtil::loadLanguageFile()

## [2.135.1] - 2020-02-19

- fixed PdfWriter::generate() to generate correct path

## [2.135.0] - 2020-02-18

- added UrlUtil::getRelativePath()

## [2.134.0] - 2020-02-17

- added absoluteUrl option to UrlUtil::removeQueryString()
- added absoluteUrl option to UrlUtil::prepareUrl()
- added RequestUtil::isNewVisitor()
- deprecated UrlUtil::isNewVisitor()

## [2.133.0] - 2020-02-06

- added label formatting for `ModelInstanceChoice`

## [2.132.0] - 2020-02-05

- added `replace_inserttag` twig filter

## [2.131.0] - 2020-02-03

- added `AnonymizerUtil` with email anonymizer
- added `anonymize_email` twig filter

## [2.130.0] - 2020-01-30

- added `FileUtil::getFolderContent()`
- added `DatabaseUtil::findResultsBy()`

## [2.129.0] - 2020-01-27

- added `FileUtil::getFileIdFromPath()`

## [2.128.1] - 2020-01-27

- added reference for php operators

## [2.128.0] - 2020-01-23

- added comparison util

## [2.127.0] - 2020-01-16

- added support for dc_multilingual 4

## [2.126.0] - 2020-01-09

- added `DatabaseUtil::findResultByPk()`
- added `DatabaseUtil::findOneResultBy()`

## [2.125.0] - 2020-01-07

- added `ContainerUtil::isDev()`

## [2.124.0] - 2019-12-13

- updated `DcaUtil::getDataContainers()` to list all database data containers
- added  `DcaUtil::getDataContainers()` option to only list database data containers
- updated dca util tests

## [2.123.1] - 2019-12-11

- added `ContainerUtil::isFrontendCron()`
- fixed `StringUtil::replaceUnicodeEmojisByHtml()`

## [2.123.0] - 2019-11-27

- replace inserttags in `FormUtil::prepareSpecialValueForOutput`

## [2.122.0] - 2019-11-26

- `CreateImageSizeCommand`

## [2.121.0] - 2019-11-21

- added video twig template

## [2.120.1] - 2019-11-14

- updated some service definitions for better symfony 4 compatibility

## [2.120.0] - 2019-11-12
- added `RsceUtil`

## [2.119.4] - 2019-11-05
- fixed module isSubModuleOf in ModuleUtil

## [2.119.3] - 2019-11-05
- fixed accordion ptable

## [2.119.2] - 2019-11-05
- fixed CfgTagModel

## [2.119.1] - 2019-10-30

### Fixed
- margins in FPDIWriter
- text length calculation in StringUtil::truncateHtml

## [2.119.0] - 2019-10-28

### Added
- support for exists() in filedata twig filter

## [2.118.0] - 2019-10-28

### Added
- copyright support for image

## [2.117.2] - 2019-10-23

### Fixed
- location util

## [2.117.1] - 2019-10-22

### Fixed
- download extension

## [2.117.0] - 2019-10-21

### Added 
- polyfills to package.json: `element-closest`, `nodelist-foreach-polyfill`

## [2.116.0] - 2019-10-10

### Added 
- ModuleUtil::getModuleClass

### Fixed
- ModuleUtil::isSubModuleOf

## [2.115.0] - 2019-10-01

### Added
- js: event-util::createEventObject

## [2.114.0] - 2019-09-30

### Added
- graceful degredation for picture (svg)

## [2.113.0] - 2019-09-20

### Removed
- choice caching from AbstractChoice for backend

## [2.112.0] - 2019-09-17

### Added
- ArrayExtension

## [2.111.0] - 2019-09-17

### Added
- StringUtil::replaceUnicodeEmojisByHtml()

## [2.110.0] - 2019-09-03

### Added
- subClass for dc operations template

### Changed
- DcaUtil::generateDcOperationsButtons() -> support for options

## [2.109.0] - 2019-09-03

### Added
- styling for backend sub records

## [2.108.0] - 2019-08-29

### Changed
- DateUtil::getFormattedDateTime() now supports translating months

### Fixed
- issues in DateUtil::getFormattedDateTime()

## [2.107.0] - 2019-08-27

### Added
- StringExtension (twig)

## [2.106.0] - 2019-08-16

### Changed
- enhanced DcaUtil::doGenerateDcOperationsButtons()

### Added
- DcaUtil::getPopupWizardLink()

## [2.105.0] - 2019-08-14

### Added
- ContainerUtil::isInstall()

## [2.104.4] - 2019-08-13

### Changed
- DcaUtil::addAuthorFieldAndCallback() has an additional parameter for prefixing the fields now

## [2.104.3] - 2019-08-08

### Fixed
- removed trailing comma in FPDIWriter (#11)

## [2.104.2] - 2019-08-07

### Fixed
- autowiring issue with FolderUtil and Symfony 4 (#9)

## [2.104.1] - 2019-08-07

### Changed
- updated tests

## [2.104.0] - 2019-08-06

### Added
- FPDIWriter

### Changed
- refactored PdfWriter

## [2.103.1] - 2019-08-05

### Fixed
- parameter name (tmp_folder instead of tmpFoldergit st)

## [2.103.0] - 2019-08-05

### Added
- FileArchiveUtil (huh.utils.file_archive)
- FolderUtil (huh.utils.folder)
- Configuration

## [2.102.0] - 2019-07-31

### Added
- FileUtil::getFileContentFromUuid()
- LocationUtil::getCoordinatesFromGpx()
- LocationUtil::getCoordinatesFromKml()
- StringUtil::convertXmlToArray()

## [2.101.1] - 2019-07-29

### Fixed
- ajax FormData issue

## [2.101.0] - 2019-07-10

### Added
- node module ajax-util set responseType

## [2.100.2] - 2019-07-09

### Fixed
- debug code in files util

## [2.100.1] - 2019-06-28

### Fixed
- js ajaxUtil for supporting objects

## [2.100.0] - 2019-06-24

### Added
- `ImageExtension::getImageGallery()`

## [2.99.1] - 2019-06-17

### Changed
- submitted data in request

## [2.99.0] - 2019-06-17

### Added
- `image_gallery` as Twig extension to show gallery items as image objects as list

## [2.98.3] - 2019-06-14

### Added
- `readableFilesize` as Twig attribute to `FileExtension.php` in TwigUtil

## [2.98.2] - 2019-06-14

### Added
- description for usage of js ajaxUtil in readme

## [2.98.1] - 2019-06-14

### Changed
- moved afterSubmit callback in ajax util to correct position

## [2.98.0] - 2019-06-13

### Changed
- moved js from component directly into bundle

## [2.97.2] - 2019-06-11

### Fixed
- service injection

## [2.97.1] - 2019-06-06

### Fixed
- `huh.utils.dca` method `addAliasToDca` now properly supports replacement of fields by considering fieldset and field separator (comma and semikolon)

## [2.97.0] - 2019-05-29

### Changed
- removed `srcset` parameter related to `source` inside `picture.html.twig` to fix mobile issues

## [2.96.0] - 2019-05-07

### Added
- `aspectRatio` parameter (boolean) support added to `lazyload` attribute inside `picture.html.twig`

## [2.95.0] - 2019-05-07

### Added
- FormUtil::getBackendFormField()

## [2.94.0] - 2019-04-29

### Changed
- replaced `html2text/html2text` with `soundasleep/html2text` due to GPL licence (`html2text/html2text`) incompatibility

## [2.93.0] - 2019-04-26

### Changed
- replaced `roderik/pwgen-php` with `hackzilla/password-generator` due to GPL licence (`roderik/pwgen-php`) incompatibility

### Added
- GNU LESSER GENERAL PUBLIC LICENSE  

## [2.91.2] - 2019-04-26

### Fixed
- database error in AccordionUtil::structureAccordionSingle()

## [2.91.1] - 2019-04-25

### Fixed
- w3c validator error in `picture.html.twig` occured by lazyload technique

## [2.91.0] - 2019-04-25

### Added
- invoke `huh.utils.listener.frontend_page` that ensure line breaks for several languages (in cs for instance one syllable words like a should stay together, e.g. `a stavět` -> `a&nbsp;stavět`)

## [2.90.3] - 2019-04-18

### Added
- add possibility to invoke custom lazy loading config into `picture.html.twig`

## [2.90.2] - 2019-04-18

### Changed 
- inject service container in DateUtil
- updated tests
- updated documentation

### Fixed
- loadDataContainer Hook error when empty database 

## [2.90.1] - 2019-04-18

### Fixed
- non-public services in ContainerUtil

## [2.90.0] - 2019-04-16

### Changed 
- made $fileExtension parameter optional in FileCache::exist() and FileCache::get()
- FileCache now uses kernel.project_dir instead of contao.web_dir for root path
- refactoring due changes in internal coding standards
- refactored service loading into Plugin class
- updated a lot of tests

### Fixed
- FileCache::getNamespace() error when namespace not initialized

### Fixed
- possible warnings in AccordionUtil

## [2.89.2] - 2019-04-09

### Changed
- `huh.utils.form` method `prepareSpecialValueForOutput` for `multiColumnEditor` formatted with linebreaks and tabs 

## [2.89.1] - 2019-04-09

### Changed
- `huh.utils.form` method `prepareSpecialValueForOutput` now supports `multiColumnEditor` bundle only

## [2.89.0] - 2019-04-08

### Changed
- `DcaUtil::getFields()`: order in label swapped for usability reasons

## [2.88.0] - 2019-04-08

### Changed
- default modal width to 1024 in `DcaUtil::getModalEditLink()` and `DcaUtil::getArchiveModalEditLink`

### Fixed
- `DcaUtil::getEditLink()` now respects symfony environment
- `DcaUtil::getModalEditLink` now respects symfony environment
- `DcaUtil::getArchiveModalEditLink` now respects symfony environment

## [2.87.4] - 2019-04-08

### Added
- `UrlUtil::getJumpToPageUrl()`
- `UrlUtil::addAutoItemToPage()`

## [2.87.3] - 2019-04-08

### Fixed
- `ModuleUtil::getModulesByType()`

## [2.87.2] - 2019-04-08

### Fixed
- `ModuleUtil::isSubModuleOf()`

## [2.87.1] - 2019-04-08

### Added
- error handling for coordinate retrieval
- `LocationUtil::computeCoordinatesInSaveCallback()`

## [2.87.0] - 2019-04-08

### Added
- tl_settings::utilsGoogleApiKey as a central point for specifying a google api key
- localizations

## [2.86.2] - 2019-03-29

### Fixed
- TemplateUtil::isTemplatePartEmpty treated null as not empty

## [2.86.1] - 2019-03-27

### Fixed
- drop `hash` and `handle` from Twig Extension (`FileExtension`), big performance impact due to additional db/file system access

## [2.86.0] - 2019-03-25

### Added
- `huh.utils.string` ensureLineBreaks() that fixes line breaks for one-syllable words in czech language (should not stand alone at the end), this is done by `huh.utils.listener.frontend_page` listener automatically

## [2.85.0] - 2019-03-25

### Added
- ModuleUtil
- DateUtil::getFormattedDateTime(), DateUtil::getFormattedDateTimeByEvent()
- DcaUtil::loadLanguageFile()
- MemberUtil::findOrCreate()

### Fixed
- MemberUtil
- DcaUtil

## [2.84.1] - 2019-03-21

### Fixed
- twig templates did not render error messages, because they were catched before in ClassUtil and Twig Extensions

## [2.84.0] - 2019-03-20

### Added
- polyfills for js

### Changed
- js generation to use webpack 0.24+

## [2.83.1] - 2019-03-15

### Fixed
- added `ignoreMethods` to prevent file access in twig `FileExtension`

## [2.83.0] - 2019-03-14

### Added
- added options support for `skippedMethods` and `ignoreMethods` in `huh.utils.class` method `jsonSerialize`
- catch Exceptions in `huh.utils.class` method `jsonSerialize` method calls in order to skip invalid method calls and continue serialization
- twig `FileExtension` ('file_data`) now skips all file content related method calls and magic getter properties (handle out of memory exceptions)

## [2.82.1] - 2019-03-14

### Changed
- added some polish translations

## [2.82.0] - 2019-03-14

### Changed
- updated dependency `tijsverkoyen/css-to-inline-styles` to ^2.2

## [2.81.2] - 2019-03-12

### Fixed
- `queryBuilder->setParameter` can't handle `.` in wildcard parameter. Replaced `.` in wildcard with `_`

## [2.81.1] - 2019-03-08

### Fixed
- added missing closing `<div>` tag in `image.html.twig` (did not tag 2.80.1)

## [2.81.0] - 2019-03-08

### Added
- FileExtension

## [2.80.1] - 2019-03-08

### Fixed
- added missing closing `<div>` tag in `image.html.twig`

## [2.80.0] - 2019-03-08

### Changed
- `image.html.twig` syntax is now compatible with `heimrichhannot/contao-speed-bundle` lazyload component from version `1.8`

## [2.75.1] - 2019-03-06

### Fixed
- `eventUtil::addDynamicEventListener()` now check that `matches` function exist in object

## [2.75.0] - 2019-03-05

### Fixed
- `eventUtil::addDynamicEventListener()` argument `scope` added (default: `document`) and moved `disableBubbling` to 5th argument
- `eventUtil::addDynamicEventListener()` now works with window eventListeners like `load` or `resize`

## [2.74.0] - 2019-02-26

### Changed
- `ImageExtension:getImage()` $template parameter no longer yields namespace, now uses `huh.utils.template` method `getTemplate`

### Added
- `RenderTwigTemplateEvent` (huh.utils.template.render) to manipulate template name and context data before rendering twig templates
- ratio, width and height parameter to picture attribute in `huh.utils.image` addToTemplateData

## [2.73.0] - 2019-02-20

### Added
- `getParentRecords` added in `huh.utils.cache.database_tree`

## [2.72.0] - 2019-02-20

### Added
- `getPreviewFromPdf` method in `huh.utils.file`

## [2.71.0] - 2019-02-20

### Added
- twig filter `image_data` in `ImageExtension` to get image data as array

## [2.70.2] - 2019-02-19

### Fixed
- compile error due calling `System:getContainer` in `TemplateUtil` constructor

## [2.70.1] - 2019-02-19

### Fixed
- `TemplateUtil::getTemplateGroup` now returns file extension in file name if not html.twig

## [2.70.0] - 2019-02-18

### Changed
- `TemplateUtil` now supports all twig formats
- `TemplateUtil::getTemplate` now throws an error if template not exist

## [2.69.1] - 2019-02-15

### Added
- `label_callback` argument `$context` in `huh.utils.choice.model_instance`

## [2.69.0] - 2019-02-14

### Added
- `label_callback` added to `$context` in order to adjust label in `huh.utils.choice.model_instance`

## [2.68.2] - 2019-02-13

### Fixed
- AccordionUtil

## [2.68.1] - 2019-02-12

### Fixed
- `huh.utils.cache.database_tree` now properly clear cache on `oncut_callback`, `ondelete_callback`, `onsubmit_callback`
- `huh.utils.template` returned wrong path for `/templates` twig templates
- Unit test errors

## [2.68.0] - 2019-02-11

### Added
- `huh.utils.cache.database_tree` to replace `Database->getChildRecords()` and provide proper caching

## [2.67.2] - 2019-02-08

### Fixed
- JavaScript webpack generation issue

### Removed
- package.json -> browserslist (now using the default option)

## [2.67.1] - 2019-02-08

### Fixed
- JavaScript: `domUtil.getAllParentNodes()`

## [2.67.0] - 2019-02-05

### Added
- `tns-lazy-img` class to `picture.html.twig` in order to fix `https://github.com/heimrichhannot/contao-tiny-slider-list-bundle` lazy load handling on ios safari

## [2.66.1] - 2019-02-04

### Added
- `huh.utils.template` method `getTemplate` returns template name plus format if no template was found

## [2.66.0] - 2019-02-04

### Added
- `huh.cache.warm_internal` service that provides an twig template cache in production mode (for performance reasons)
- `huh.utils.template` method `getAllTemplates` that provides better cache handling, invoked on every contao request as `initializeSystem` Hook

### Fixed
- `huh.utils.template` template caching, improves performance on method `getTemplate` by factor 4

## [2.65.2] - 2019-01-29

### Fixed
- ModelUtil columns array issue

## [2.65.1] - 2019-01-28

### Fixed
- ModelInstanceChoice::collect()

## [2.65.0] - 2019-01-25

### Removed
- `DcaUtil::addDcMultilingualSupport`
- `DcaUtil::addDcMultilingualTranslatableAliasEval`

-> please use [heimrichhannot/contao-dc-multilingual-utils-bundle](https://github.com/heimrichhannot/contao-dc-multilingual-utils-bundle) instead

## [2.64.1] - 2019-01-24

### Fixed
- lazyload in `picture.html.twig` only set `height` (do not set `width`, otherwise lazyload will break)

## [2.64.0] - 2019-01-24

### Added
- `StringUtil::camelCaseToSnake`

## [2.63.0] - 2019-01-24

### Added
- DcaUtil::addDcMultilingualTranslatableAliasEval
- support for table prefixed fields in DatabaseUtil::composeWhereForQueryBuilder()

### Fixed
- ModelUtil::findParentsRecursively() order issue

## [2.62.0] - 2019-01-24

### Changed
- lazyload in `picture.html.twig` now uses `width` and `height` styles instead of `padding-bottom`
- removed `attributes` from anchor inside `image.html.twig` and added `linkAttributes`

## [2.61.0] - 2019-01-23

### Changed 
- js workflow optimized
- yarn dependency `contao-utils-bunlde` to `@hundh/contao-utils-bundle` 

## [2.60.8] - 2019-01-22

### Fixed
- js

## [2.60.7] - 2019-01-18

### Fixed
- correct calculation of padding for portrait format images in picture template

## [2.60.6] - 2019-01-08

### Changed
- names of util params in singular

## [2.60.5] - 2019-01-07

### Fixed
- adept fixes from 2.60.4 for `TemplateUtil::getBundleTemplate` and `TemplateUtil::getTemplate`

## [2.60.4] - 2019-01-07

### Fixed
- `TemplateUtil::getTemplateGroup` now finds all twig templates in views, not just `html.twig`

## [2.60.3] - 2018-12-21

### Fixed
- set class from img on `image-wrapper` in `picture.html.twig` to handle padding-bottom dimensions (border)

## [2.60.2] - 2018-12-21

### Fixed
- added styles to `picture.html.twig` that adjusts padding-bottom for media query image sizes

## [2.60.1] - 2018-12-19

### Fixed
- js utils

## [2.60.0] - 2018-12-19

### Fixed
- refactoring for js utils

## [2.59.2] - 2018-12-17

### Fixed
- Make `AccordionUtil` work with multiple articles per page

## [2.59.1] - 2018-12-17

### Fixed
- Make `AccordionUtil` work with multiple articles per page

## [2.59.0] - 2018-12-17

### Added
- DcaUtil::addDcMultilingualSupport()

### Fixed
- ModelUtil::fixTablePrefixForDcMultilingual() to support "order" in Model's $options

## [2.58.0] - 2018-12-14

### Added
- `attributes` parameter to `download.html.twig`

## [2.57.0] - 2018-12-13

### Changed
- size parameter in ImageExtension now also supports serialized strings

## [2.56.1] - 2018-12-12

### Fixed
- ModelUtil::addPublishedCheckToModelArrays()

## [2.56.0] - 2018-12-12

### Added
- `figureAttributes` attribute to `image.html.twig`

## [2.55.0] - 2018-12-12

### Added
- ModelUtil::callModelMethod(), ModelUtil::addPublishedCheckToModelArrays()

## [2.54.0] - 2018-12-12

### Added
- DcaUtil::getDCTable()

### Fixed
- localization issue in FormUtil::prepareSpecialValueForOutput()

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
- picture template twig error if title not defined
- enhanced code documentation

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

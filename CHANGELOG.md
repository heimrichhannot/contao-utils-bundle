# Changelog

All notable changes to this project will be documented in this file.

## [2.233.1] - 2023-12-19
- Fixed: mce issue in FormUtil

## [2.233.0] - 2023-12-13
- Added: Utils/ClassUtil

## [2.232.0] - 2023-11-14
- Changed: backported Utils/DatabaseUtil from v3 to v2
- Deprecated: old DatabaseUtil class

## [2.231.0] - 2023-10-24
- Added: Utils/ModelUtil::findParentsRecursively()
- Deprecated: ModelUtil::findParentsRecursively()

## [2.230.1] - 2023-10-13
- Fixed: missing image custom properties

## [2.230.0] - 2023-10-12
- Added: AuthorField class as new mechanic to add author field
- Deprecated: DcaUtil::addAuthorFieldAndCallback()

## [2.229.4] - 2023-09-15
- Fixed: missing copyright for image template

## [2.229.3] - 2023-09-15
- Fixed: get file model from image path to render caption

## [2.229.2] - 2023-09-07
- Fixed: undefined method exception in RequestCleaner

## [2.229.1] - 2023-09-06
- Fixed: PHP8 warning fix for `getImageData()` in `Twig\ImageExtension.php`
- Changed: method collection/reflection, cosmetic improvements in `Classes\ClassUtil.php`

## [2.229.0] - 2023-08-28
- Changed: removed request bundle dependency

## [2.228.2] - 2023-08-17
- Fixed: missing alt text in image twig tag

## [2.228.1] - 2023-07-04
- Fixed: compatibility issue with symfony 3

## [2.228.0] - 2023-05-22
- Added: absolute url option to RequestUtil::generateBackendRoute() ([#65])

## [2.227.1] - 2023-04-27
- Fixed: warnings
- Deprecated: StringUtil::startWith and StringUtil::endsWith

## [2.227.0] - 2023-04-05
- Changed: reduced usage of request bundle ([#64])
- Deprecated: UrlUtil::removeQueryStringParameterToUrl() (renamed to removeQueryStringParameterFromUrl())
- Fixed: typo in UrlUtil::removeQueryStringParameterToUrl()
- Fixed: various warnings
- Fixed: various deprecations

## [2.226.1] - 2023-03-20
- Fixed: warning in ImageUtil

## [2.226.0] - 2023-02-24
- Added: Check Util folder with phpstan ([#63])
- Added: Util/DcaUtil::getDcaFields() ([#62])
- Deprecated: DcaUtil::getFields()

## [2.225.0] - 2023-01-25
- Added: UrlUtil to Utils service ([#61])

## [2.224.2] - 2023-01-20
- Fixed: overwritten picture property of ImageExtension

## [2.224.1] - 2023-01-18
- Fixed: array index issue

## [2.224.0] - 2023-01-02
- Added: HtmlUtil::generateDataAttributesString() ([#60])

## [2.223.3] - 2022-12-23
- Fixed: php8-related bug

## [2.223.2] - 2022-12-21
- Fixed: Exception if Choice is used as options callback

## [2.223.1] - 2022-12-20
- Fixed: Choices cache not taking locale into account
- Fixed: used deprecated ServiceSubscriber if available in AbstractServiceSubscriber

## [2.223.0] - 2022-11-09
- Added: HtmlUtil ([#59])

## [2.222.1] - 2022-11-07
- Fixed: used wrong argument in entity finder for blocks

## [2.222.0] - 2022-10-17
- Changed: migrated AccordionUtil::structureAccordionSingle()
- Fixed: used non namespaced twig error class

## [2.221.2] - 2022-10-17
- Changed: updated dependencies
- Fixed: accordion util not respect element start and end date ([#58])
- Fixed: ci pipeline used outdated actions


## [2.221.1] - 2022-10-05
- Fixed: [IcsUtil] set endDate to startDate if not set

## [2.221.0] - 2022-09-27
- Added: entity finder helper ([#53])
- Added: news support to entity finder ([#53])

## [2.220.1] - 2022-09-20
- Fixed: 2.220.0 breaks contao 4.4 support

## [2.220.0] - 2022-09-20
- Changed: Migrate routing util to utils ([#56])
- Fixed: array index issue

## [2.219.0] - 2022-09-13
- Added: RequestUtil::isIndexPage() ([#55])
- Fixed: font name evaluation in FPDFIWriter

## [2.218.1] - 2022-07-18
- Fixed: PHP 8 warning

## [2.218.0] - 2022-07-15
- Added: Utils::user::findActiveUsersByGroup() (migrated from user util) ([#51])

## [2.217.2] - 2022-06-07
- Fixed: patchwork utf8 class used ([#50])

## [2.217.1] - 2022-06-07
- Fixed: typed properties in FileUtil

## [2.217.0] - 2022-05-31
- Added: Utils::file::getPathFromUuid()
- Added: Utils::request::getCurrentRootPageModel()
- Fixed: [DatabaseUtil] invalid field name added to dca
- Fixed: Warnings in php 8
- Deprecated: FileUtils::getPathFromUuid();

## [2.216.0] - 2022-05-17
- Added: ArrayUtil::insertAfterKey() ([#47])
- Fixed: DcaUtil::doGenerateDcOperationsButtons() for 4.13 ([#46])

## [2.215.2] - 2022-05-09
- Fixed: display special chars in alt attribute

## [2.215.1] - 2022-05-05
- Fixed: incompatiblity with symfony 5

## [2.215.0] - 2022-05-03
- Changed: Update utils class structure ([#44])
- Changed: Migrate ArrayUtil::removeValue ([#45])

## [2.214.0] - 2022-04-28
- Added: Utils/RequestUtil::getBaseUrl()
- Changed: migrated RequestUtil::isNewVisitor() to Utils/RequestUtil
- Deprecated: RequestUtil

## [2.213.0] - 2022-04-08
- Added: [Entity finder] allow search for inserttags from event (added `ExtendEntityFinderEvent::addInserttag()`)
- Fixed: [Entity finder] only one parent found for each entity

## [2.212.1] - 2022-03-28
- Fixed: a potential error upon cache warmup and a missing system/tmp folder ([#43], [@qzminski])

## [2.212.0] - 2022-03-22
- Added: entity finder ([#42])
- Fixed: some incompatibilities with different symfony versions ([#42])

## [2.211.0] - 2022-03-07
- Added: Utils/DcaUtils::getPaletteFields()
- Changed: updated test setup

## [2.210.1] - 2022-02-28
- Fixed: exception in DcaUtil
- Fixed: deprecation
- Fixed: missing parameter in DcaUtil test

## [2.210.0] - 2022-02-23
- Added: DcaUtils::explodePalette() in Utils namespace

## [2.209.6] - 2022-02-16

- Fixed: array index issues in php 8+

## [2.209.5] - 2022-02-16

- Fixed: array index issues in php 8+

## [2.209.4] - 2022-02-15

- Fixed: array index issues in php 8+

## [2.209.3] - 2022-02-14

- Fixed: array index issues in php 8+

## [2.209.2] - 2022-02-14

- Fixed: array index issues in php 8+

## [2.209.1] - 2022-02-10

- Fixed: `DatabaseUtil::composeWhereForQueryBuilder()` for contao 4.13+

## [2.209.0] - 2022-02-09
- Fixed: `ContainerUtil::isMaintenanceModeActive()` for contao 4.13+

## [2.208.1] - 2022-01-18
- Fixed: invalid composer.json

## [2.208.0] - 2022-01-18
- Added: AccordionUtil in Utils namespace
- Changed: rewrote AccordionUtil::structureAccordionStartStop() to support nested accordions ([#40])
- Fixed: test configuration for Utils and AbstractServiceSubscriber classes

## [2.207.0] - 2021-12-21
- Added: ModelUtil to Utils service (most services are migrated, but not all now)
- Added: UserUtil to Utils service (not all services migrated)
- Added: ArrayUtil to Utils service (with one method)
- Deprecated: most ModelUtil methods
- Fixed: coverage report used contao 4.4
- Fixed: some test setup issues

## [2.206.1] - 2021-10-13
- Fixed: contao 4.4 incompatible code in DcaUtil 

## [2.206.0] - 2021-10-13

- Added: RequestUtil::getCurrentPageModel() ([#37])
- Added: AbstractServiceSubscriber to make service subscriber compatible to symfony 3, 4 and 5 ([#39])
- Changed: ContainerUtil::isPreviewMode() now uses TokenChecker::isPreviewMode() where available (Contao 4.5+) ([#37])
- Changed: Refactored ContainerUtil and Utils class inheriting from AbstractServiceSubscriber
- Fixed: failing tests ([#37])
- Fixed: remove FrontendPageListener as it add linebreak to czech language pages with many side effects, that must be an optional feature. If you need such a functionality, please add a listener by yourself! ([#38])
- Fixed: service subscriber not registered correctly ([#39])

## [2.205.3] - 2021-10-08

- Fixed: UserUtil::findActiveByGroups(#35)

## [2.205.2] - 2021-09-27

- Added: missing default value for author field

## [2.205.1] - 2021-09-24

- Changed: separator character from `_` to `-` (file sanitize)

## [2.205.0] - 2021-09-22

- Added: config parameter `skipReplaceInsertTags` in `FormUtil::prepareSpecialValueForOutput()`

## [2.204.2] - 2021-09-17

- Fixed: visibility of `FileUtil::getParentFoldersByUuid()`

## [2.204.1] - 2021-09-15

- Fixed: preview mode for contao 4.9

## [2.204.0] - 2021-09-03

- Added: new option `selectFields` for `DatabaseUtil::findResultByPk()`, `DatabaseUtil::findOneResultBy()`, `DatabaseUtil::findResultsBy()`
- Changed: enhanced ContainerUtil documentation
- Deprecated: deprecated the old StringUtil class as whole
- Fixed: issues with CI

## [2.203.3] - 2021-08-17

- Fixed: `CreateImageSizeItemsCommand`

## [2.203.2] - 2021-08-17

- Changed: Refactored `CreateImageSizeItemsCommand` -> external method now available

## [2.203.1] - 2021-08-11

- Fixed: SalutationUtil methods for "other" gender

## [2.203.0] - 2021-08-11

- Added: translations for "other" gender

## [2.202.4] - 2021-08-10

- Fixed: `DcaUtil::addAuthorFieldAndCallback()` -> default value for author is not 0 again (BC)

## [2.202.3] - 2021-07-26

- fixed palette manipulator handling in `DcaUtil::flattenPaletteForSubEntities()`

## [2.202.2] - 2021-07-26

- fixed palette manipulator handling in `DcaUtil::flattenPaletteForSubEntities()`

## [2.202.1] - 2021-07-26

- fixed palette handling issues in `DcaUtil::flattenPaletteForSubEntities()` by using `PaletteManipulator`
- fixed return object in `DatabaseUtil::update()` and `DatabaseUtil::delete()`

## [2.202.0] - 2021-07-20

- added twig filter `|bin2uuid` in order to convert a binary uuid to a textual one

## [2.201.0] - 2021-07-20

- added documentation for command `huh:utils:create-image-size-items`

## [2.200.0] - 2021-07-15

- enhanced `ModelInstanceChoice` to respect more title fields and contain the ID

## [2.199.1] - 2021-07-15

- fixed new author type for `DcaUtil::addAuthorFieldAndCallback()`: php session id (disabled to readonly)

## [2.199.0] - 2021-07-15

- added new author type for `DcaUtil::addAuthorFieldAndCallback()`: php session id (not changeable in backend; only
  visible in backend if it had been set before in frontend -> then readonly)

## [2.198.0] - 2021-07-14

- added `DcaUtil::isSubPaletteField()`
- added `DcaUtil::getSubPaletteFieldSelector()`

## [2.197.0] - 2021-07-13

- added possibility to add a custom database object to `DatabaseUtil::insert()`, `DatabaseUtil::update()`
  and `DatabaseUtil::delete()`

## [2.196.3] - 2021-07-09

- fixed bug in DcaUtil::aliasExist()

## [2.196.2] - 2021-07-07

- fixed bug in deprecated StringUtil::camelCaseToDashed()

## [2.196.1] - 2021-07-05

- fixed install issue with symfony 5

## [2.196.0] - 2021-07-01

- Add utils service and migrate services to Util namespace ([#24])
- refactored ClassUtil and ModelUtil to use dependency injection
- updates some test and skipped some tests to make github action working

## [2.195.1] - 2021-06-29

- fixed twig filter `|file_content`

## [2.195.0] - 2021-06-29

- added Polish translations

## [2.194.1] - 2021-06-24

- enhanced `DcaUtil::generateAlias()` (now supports customizable alias field name)

## [2.194.0] - 2021-06-18

- added `DcaUtil::getCurrentPaletteName()`

## [2.193.1] - 2021-06-09

- fixed twig filter `|file_content`

## [2.193.0] - 2021-06-01

- added `jsonPost()` in javascript ajax util
- fixed issues in js ajax util

## [2.192.2] - 2021-05-28

- fixed js method `DomUtil::getTextWithoutChildren()`

## [2.192.1] - 2021-05-17

- fixed hours tranformation for ISO8601 (#31)

## [2.192.0] - 2021-04-16

- added `fields` option for `DcaUtil::setFieldsToReadOnly()`

## [2.191.0] - 2021-03-23

- added params for operators IN and NOT IN in DatabaseUtil::composeWhereForQueryBuilder (#29)

## [2.190.0] - 2021-03-11

- added ImageUtil::prepareImage()

## [2.189.0] - 2021-03-10

- experimental allowed php 8
- deprecated PDFCreator in favor of PDFCreator library
- moved from samidoc to phpDocumentator
- fixed deprecation in Configuration class

## [2.188.10] - 2021-02-22

- fixed missing symfony/config component

## [2.188.9] - 2021-02-15

- avoid replaceInsertTags getting invalid path

## [2.188.8] - 2021-02-15

- fixed choice cache (not run in the constructor anymore)

## [2.188.7] - 2021-02-09

- fixed autowiring for UserUtil and MemberUtil

## [2.188.6] - 2021-02-01

- fixed twig picture template if width or height are null

## [2.188.5] - 2021-01-18

- added PersonTrait to UserUtil and MemberUtil, added tests for PersonTrait (#26)

## [2.188.4] - 2021-01-18

- **BC BREAK**: fixed error in `DatabaseUtil::composeWhereForQueryBuilder()` -> `IN` and `NOT IN` statement with empty
  values leads to an unfullfillable condition, not to skipping the filter anymore (since it was a bug, the bc break was
  necessary)

## [2.188.3] - 2021-01-15

- fixed sorting in `ModelInstanceChoice` to be a natural sorting (switched asort to natcasesort)

## [2.188.2] - 2021-01-11

- fixed missing public service attributes for twig extensions (contao 4.9+)

## [2.188.1] - 2020-12-22

- fixed random fields added in loadDataContainer hooks not added to the database (#25)

## [2.188.0] - 2020-12-18

- added support for page specific date/time formats in DateUtil

## [2.187.0] - 2020-12-15

- started preparation for next major version with a new bundle and extension class (old ones are still available, but
  should not be referenced anymore, see [Upgrade guide](UPGRADE.md)) (#23)
- [BEHAVIOR CHANGE] added configuration option for databaseTreeCache warmer, cache warmer for databasetree cache is not
  executed by default anymore, but can be activated by configuration, see [Readme](README.md) (#23)
- added option to disabled the inclusion of utils bundle assets (#23)
- updated encore bundle integration, minimum supported encore bundle version is now 1.5

## [2.186.0] - 2020-12-15

- fixed DownloadExtension (see https://github.com/heimrichhannot/contao-utils-bundle/issues/22)

## [2.185.0] - 2020-11-30

- added DatabaseUtil::createWhereForSerializedBlob() option parameter and inline_values option

## [2.184.1] - 2020-11-25

- fixed loading attribute has no default value in picture.html.twig
- enhance an annotation in ModelUtil

## [2.184.0] - 2020-11-24

- added `DateUtil::convertSecondsToHumanReadableFormat()`

## [2.183.0] - 2020-11-23

- added support for `loading` attribute if set from outside (lazy loading)

## [2.182.1] - 2020-11-10

- added a parameter to `DcaUtil::setDefaultsFromDca()` for BC reasons

## [2.182.0] - 2020-11-10

- fixed `DcaUtil::setDefaultsFromDca()` to also respect sql default values

## [2.181.4] - 2020-11-05

- added check for empty request in `ContainerUtil` (e.g. in command situations)

## [2.181.3] - 2020-10-28

- fixed `UrlUtil::getBaseUrl` to enable non-app_dev.php base url for versions later than 4.8

## [2.181.2] - 2020-10-13

- fixed twig image filter not allowed image size to be an id or name

## [2.181.1] - 2020-10-06

- modified autowiring for InsertTagsListener

## [2.181.0] - 2020-10-06

- refactored InsertTagsListener
- added Tests for InsertTagsListener

## [2.180.2] - 2020-10-05

- changed template rendering in InsertTagsListener::replaceTwigTag() to TemplateUtil::renderTwigTemplate

## [2.180.1] - 2020-09-30

- fix remove dump from image_gallery template

## [2.180.0] - 2020-09-28

- added `IcsUtil`

## [2.179.0] - 2020-09-22

- added `ContentUtil`

## [2.178.2] - 2020-09-18

- do not throw error when calling protected or private methods in ClassUtil::jsonSerialize() when ignoreMethodVisibility
  is set to true

## [2.178.1] - 2020-09-15

- added state as possibility for `LocationUtil::computeCoordinatesByString()`

## [2.178.0] - 2020-09-09

- js: added `GeneralUtil.runRecursiveFunction()`

## [2.177.6] - 2020-08-31

- fixed class inheritance for CfgTagModel to respect the one existing

## [2.177.5] - 2020-08-26

- added missing default check for includeCopyright in `image.html.twig`

## [2.177.4] - 2020-08-25

- fixed uncatched errors in DcaUtil::getConfigByArrayOrCallbackOrFunction() when used in frontend and contao 4.9

## [2.177.3] - 2020-08-20

- fixed caption and copyrights issue in `image.html.twig` -> the flag `includeCopyright` has to be added
  to `image.html.twig` in order to print the copyright without a caption set

## [2.177.2] - 2020-08-20

- removed database tree cache for `tl_page` from utils-bundle since only needed for contao-blocks
- fixed missing prefixes in database tree cache

## [2.177.1] - 2020-08-19

- fixed caption and copyrights issue

## [2.177.0] - 2020-08-19

- added callbacks to PdfCreator

## [2.176.1] - 2020-08-19

- fixed MpdfCreator font directory support

## [2.176.0] - 2020-08-18

- added PdfCreator as replacement for PdfWriterpull

## [2.175.2] - 2020-08-13

- show twig template start and stop comments in dev mode

## [2.175.1] - 2020-08-12

- fixed comparison issue in `DatabaseUtil::doBulkInsert()`

## [2.175.0] - 2020-08-11

- added support for divers gender-based salutations

## [2.174.0] - 2020-08-07

- added possibility to add a custom backend route in `DcaUtil::getPopupWizardLink()`

## [2.173.1] - 2020-07-22

- added alias for curlRequestUtil

## [2.173.0] - 2020-07-22

- DcaUtil::generateAlias() now accepts null as table parameter to skip unique check

## [2.172.1] - 2020-07-21

- fixed a error with php version prior to 7.4

## [2.172.0] - 2020-07-21

- added DcaUtil::aliasExist()

## [2.171.5] - 2020-07-20

- fixed UrlUtil::getCurrentUrl() options parameter not optional

## [2.171.4] - 2020-07-16

- fixed `StringExtension::autolink()` (German dates like 15.7. have been translated to links)

## [2.171.3] - 2020-07-16

- fixed unicode characters for `StringUtil::replaceUnicodeEmojisByHtml()` to also contain skin tones

## [2.171.2] - 2020-07-15

- added latest unicode characters for `StringUtil::replaceUnicodeEmojisByHtml()`

## [2.171.1] - 2020-07-06

- fixed type hinting

## [2.171.0] - 2020-06-30

- added ignoreLogin to `MemberUtil::findActiveByGroups()`

## [2.170.0] - 2020-06-30

- fixed attributes issue in `image.html.twig` -> now link gets its correct `attributes`; "wrong" `linkAttributes` is
  still in place for compatibility reasons
- fixed lightbox issues in `ImageUtil::addToTemplate()`

## [2.169.2] - 2020-06-23

- fixed `DatabaseUtil::findResultsBy()` to accept also null as columns and values

## [2.169.1] - 2020-06-23

- fixed `DcaUtil::generateAlias()`

## [2.169.0] - 2020-06-23

- added `ContainerUtil::isPreviewMode()`

## [2.168.2] - 2020-06-23

- fixed `DcaUtil::generateAlias()`

## [2.168.1] - 2020-06-23

- fixed `DcaUtil::generateAlias()`

## [2.168.0] - 2020-06-23

- fixed `DcaUtil::generateAlias()`

## [2.167.0] - 2020-06-22

- added `FileUtil::getExtensionFromFileContent()`
- added `FileUtil::getExtensionByMimeType()`
- fixed `FileUtil::retrieveFileContent()`

## [2.166.0] - 2020-06-19

- added `FileUtil::retrieveFileContent()`

## [2.165.0] - 2020-06-15

- added option to pass multiple tables into `DcaUtil::generateAlias()` as a comma separated list

## [2.164.2] - 2020-06-10

- fixed warning if no result in `DcaUtil::generateAlias()`

## [2.164.1] - 2020-05-29

- fixed size issue in twig image filter

## [2.164.0] - 2020-05-25

- added new twig filter `|file_content` (takes uuid as binary or string)

## [2.163.0] - 2020-05-13

- added new tests to `TestExtension`
- fixed copyright issue in `image.html.twig`

## [2.162.0] - 2020-04-28

- revoked 2.161.0 and 2.161.1 due to problems in contao 4.9 -> pages can't be saved anymore

## [2.161.0] - 2020-04-24

- added TemplateLocator class
- enhanced UtilsCacheWarmer
- some code enhancements

## [2.160.0] - 2020-04-22

- added `TestExtension` for checking types in twig (e.g. `if [] is string` or `if '1' is numeric`)

## [2.159.1] - 2020-04-22

- fixed type hinting for `DateUtil::generateAlias()`

## [2.159.0] - 2020-04-21

- added `choices.yml` containing the service definitions for the choices

## [2.158.0] - 2020-04-21

- added `DatabaseUtil::beginTransaction()` and `DatabaseUtil::commitTransaction()`

## [2.157.3] - 2020-04-21

- fixed types in `DatabaseUtil`

## [2.157.2] - 2020-04-20

- fixed types in replaceInsertTags() from StringUtil

## [2.157.1] - 2020-04-16

- removed `select()` from `DatabaseUtil` as it's already covered by the various `findBy` methods

## [2.157.0] - 2020-04-16

- added `DcaUtil::getNewSortingPosition()`

## [2.156.1] - 2020-04-14

- fixed session bug for contao 4.9 in `LocationUtil`

## [2.156.0] - 2020-04-14

- added `DatabaseUtil::select()`

## [2.155.2] - 2020-04-09

- fixed PageUtil service definition for symfony 4

## [2.155.1] - 2020-04-08

- added empty check for `imageSize` in `ImageUtil`

## [2.155.0] - 2020-04-08

- added `PageUtil`

## [2.154.0] - 2020-04-06

- added attributes and linkText option to DcaUtil::getPopupWizardLink()

## [2.153.0] - 2020-04-06

- partly rewrote DcaUtil::getPopupWizardLink()
- deprecated DcaUtil::getPopupWizardLink() string as first parameter
- updated documentation
- updated tests

## [2.152.1] - 2020-04-06

- fixed `StringUtil::replaceInsertTags()`

## [2.152.0] - 2020-04-03

- added `StringUtil::replaceInsertTags()`

## [2.151.1] - 2020-03-26

- fixed `UrlUtil::getBaseUrl()`

## [2.151.0] - 2020-03-26

- added `ContainerUtil::isMaintenanceModeActive()`

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

- `huh.utils.dca` method `addAliasToDca` now properly supports replacement of fields by considering fieldset and field
  separator (comma and semikolon)

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

- replaced `roderik/pwgen-php` with `hackzilla/password-generator` due to GPL licence (`roderik/pwgen-php`)
  incompatibility

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

- invoke `huh.utils.listener.frontend_page` that ensure line breaks for several languages (in cs for instance one
  syllable words like a should stay together, e.g. `a stavět` -> `a&nbsp;stavět`)

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

- drop `hash` and `handle` from Twig Extension (`FileExtension`), big performance impact due to additional db/file
  system access

## [2.86.0] - 2019-03-25

### Added

- `huh.utils.string` ensureLineBreaks() that fixes line breaks for one-syllable words in czech language (should not
  stand alone at the end), this is done by `huh.utils.listener.frontend_page` listener automatically

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
- catch Exceptions in `huh.utils.class` method `jsonSerialize` method calls in order to skip invalid method calls and
  continue serialization
- twig `FileExtension` ('file_data`) now skips all file content related method calls and magic getter properties (handle
  out of memory exceptions)

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

- `image.html.twig` syntax is now compatible with `heimrichhannot/contao-speed-bundle` lazyload component from
  version `1.8`

## [2.75.1] - 2019-03-06

### Fixed

- `eventUtil::addDynamicEventListener()` now check that `matches` function exist in object

## [2.75.0] - 2019-03-05

### Fixed

- `eventUtil::addDynamicEventListener()` argument `scope` added (default: `document`) and moved `disableBubbling` to 5th
  argument
- `eventUtil::addDynamicEventListener()` now works with window eventListeners like `load` or `resize`

## [2.74.0] - 2019-02-26

### Changed

- `ImageExtension:getImage()` $template parameter no longer yields namespace, now uses `huh.utils.template`
  method `getTemplate`

### Added

- `RenderTwigTemplateEvent` (huh.utils.template.render) to manipulate template name and context data before rendering
  twig templates
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

- `tns-lazy-img` class to `picture.html.twig` in order to
  fix `https://github.com/heimrichhannot/contao-tiny-slider-list-bundle` lazy load handling on ios safari

## [2.66.1] - 2019-02-04

### Added

- `huh.utils.template` method `getTemplate` returns template name plus format if no template was found

## [2.66.0] - 2019-02-04

### Added

- `huh.cache.warm_internal` service that provides an twig template cache in production mode (for performance reasons)
- `huh.utils.template` method `getAllTemplates` that provides better cache handling, invoked on every contao request
  as `initializeSystem` Hook

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

-> please
use [heimrichhannot/contao-dc-multilingual-utils-bundle](https://github.com/heimrichhannot/contao-dc-multilingual-utils-bundle)
instead

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

- new methods to DateUtil: isMonthInDateFormat(), isDayInDateFormat(), isYearInDateFormat(), getMonthTranslationMap(),
  getShortMonthTranslationMap(), translateMonthsToEnglish(), translateMonths()

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

- surround `$file->imageSize` in `ImageUtil::addToTemplateData` with catch block, to prevent error messages for non
  existing images to stop working site

## [2.30.0] - 2018-09-25

### Added

- Inserttag `twig` (Example:`{{twig::logo.html.twig::a:1:{s:3:"foo";s:3:"bar";}}}`) to render twig templates from
  inserttags with custom serialized data


[@qzminski]: https://github.com/qzminski
[#65]: https://github.com/heimrichhannot/contao-utils-bundle/pull/65
[#64]: https://github.com/heimrichhannot/contao-utils-bundle/pull/64
[#63]: https://github.com/heimrichhannot/contao-utils-bundle/pull/63
[#62]: https://github.com/heimrichhannot/contao-utils-bundle/pull/62
[#61]: https://github.com/heimrichhannot/contao-utils-bundle/pull/61
[#60]: https://github.com/heimrichhannot/contao-utils-bundle/pull/60
[#59]: https://github.com/heimrichhannot/contao-utils-bundle/pull/59
[#56]: https://github.com/heimrichhannot/contao-utils-bundle/pull/56
[#55]: https://github.com/heimrichhannot/contao-utils-bundle/pull/55
[#53]: https://github.com/heimrichhannot/contao-utils-bundle/pull/53
[#51]: https://github.com/heimrichhannot/contao-utils-bundle/pull/51
[#50]: https://github.com/heimrichhannot/contao-utils-bundle/pull/50
[#47]: https://github.com/heimrichhannot/contao-utils-bundle/pull/47
[#46]: https://github.com/heimrichhannot/contao-utils-bundle/pull/46
[#45]: https://github.com/heimrichhannot/contao-utils-bundle/pull/45
[#44]: https://github.com/heimrichhannot/contao-utils-bundle/pull/44
[#43]: https://github.com/heimrichhannot/contao-utils-bundle/pull/43
[#42]: https://github.com/heimrichhannot/contao-utils-bundle/pull/42
[#40]: https://github.com/heimrichhannot/contao-utils-bundle/pull/40
[#38]: https://github.com/heimrichhannot/contao-utils-bundle/pull/38
[#37]: https://github.com/heimrichhannot/contao-utils-bundle/pull/37
[#24]: https://github.com/heimrichhannot/contao-utils-bundle/pull/24

# Changelog

All notable changes to this project will be documented in this file.

## [3.10.0] - 2026-01-22
- Added: Option to adjust the field where the alias is generated from (instead of overriding the complete method) ([#106](https://github.com/heimrichhannot/contao-utils-bundle/pull/106))
- Added: AliasFieldConfiguration::setGenerateAliasCallback() ([#106](https://github.com/heimrichhannot/contao-utils-bundle/pull/106))
- Changed: allow symfony 7 ([#106](https://github.com/heimrichhannot/contao-utils-bundle/pull/106))
- Changed: drop doctrine 2 support ([#106](https://github.com/heimrichhannot/contao-utils-bundle/pull/106))
- Deprecated: AliasFieldConfiguration::setAliasExistCallback() since the name is incorrect. Use `setGenerateAliasCallback` instead ([#106](https://github.com/heimrichhannot/contao-utils-bundle/pull/106))

## [3.9.5] - 2025-12-11
- Fixed: exception in entity finder with pages that need item

## [3.9.4] - 2025-10-02
- Fixed: issue with author field default value in some cases

## [3.9.3] - 2025-09-30
- Fixed: implicitly nullable parameter deprecated errors

## [3.9.2] - 2025-07-18
- Changed: moved author dca field generation to own method 

## [3.9.1] - 2025-07-18
- Fixed: issue in UserUtil

## [3.9.0] - 2025-05-20
- Added: StaticArrayUtils::filterByPrefixes ([#100](https://github.com/heimrichhannot/contao-utils-bundle/pull/100))
- Added: AliasField ([#102](https://github.com/heimrichhannot/contao-utils-bundle/pull/102))

## [3.8.0] - 2025-04-14
- Changed: Url Util Improvements by @ericges in https://github.com/heimrichhannot/contao-utils-bundle/pull/94
- Fixed: EntityFinderHelper.php by @ericges in https://github.com/heimrichhannot/contao-utils-bundle/pull/95

## [3.7.1] - 2025-03-20
- Changed: enhance code quality by using rector
- Fixed: removed usage of deprecated constants
- Deprecated: ContainerUtils::isPreviewMode()

## [3.7.0] - 2025-03-19
- Changed: enhance entity finder with universal entity support fallback ([#93](https://github.com/heimrichhannot/contao-utils-bundle/pull/93))
- Deprecated: ExtendEntityFinderEvent

## [3.6.0] - 2024-11-27
- Added: DcaFieldConfiguration can now set the field's eval options
- Added: Unit test coverage

## [3.5.0] - 2024-11-15
- Added: BackendUiUtil with popupWizardLink method ([#86](https://github.com/heimrichhannot/contao-utils-bundle/pull/86))

## [3.4.1] - 2024-10-20
- Fixed: compatibility issues with symfony 7 and contao 5.4 ([#85](https://github.com/heimrichhannot/contao-utils-bundle/issues/85))

## [3.4.0] - 2024-04-11
- Added: more options to DcaFields

## [3.3.0] - 2024-03-26
- Changed: `dca()->getDcaFields()` move `GetDcaFieldsOptions` constructor call to method body.
- Added: Static array and class utils within a new namespace for static utilities.
    (`StaticUtil\StaticArrayUtil` and `StaticUtil\StaticClassUtil`)
- Added: `StaticUtil\SUtils` with `::array()` and `::class()` to locate static array- and class-utilities.
- Added: `dca()->executeCallback(...)` to ease execution of callback-arrays and closures.
- Added: `FormatterUtil` (`$utils->formatter()`) with `formatter()->formatDcaFieldValue(...)` as a successor to Utils v2's
    [FormUtil::prepareSpecialValueForOutput()](https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Form/FormUtil.php#L99).
- Deprecated: `ArrayUtil::insertBeforeKey` in favour of `SUtils::array()->insertBeforeKey`.
- Deprecated: `ArrayUtil::insertAfterKey` in favour of `SUtils::array()->insertAfterKey`.
- Deprecated: `ArrayUtil::removeValue` in favour of `SUtils::array()->removeValue`.
- Deprecated: `ClassUtil::classImplementsTrait` in favour of `SUtils::class()->hasTrait`.

## [3.2.0] - 2024-03-18
- Changed: `UrlUtil::addQueryStringParameterToUrl()` and `UrlUtil::removeQueryStringParameterToUrl()`
    may now take multiple query parameters as array to add or remove respectively.
- Fixed: CreateWhereForSerializedBlobResult::createInlineOrWhere() return invalid query ([#79](https://github.com/heimrichhannot/contao-utils-bundle/pull/79))
- Deprecated: `StringUtil::removeLeadingString()` and `StringUtil::removeTrailingString()`

## [3.1.1] - 2024-03-01
- Fixed: exception

## [3.1.0] - 2024-03-01
- Added: DateAddedField ([#74](https://github.com/heimrichhannot/contao-utils-bundle/pull/74))

## [3.0.0] - 2024-02-19
This version is a complete reworked version of utils bundle. 
The goal was to have a non-inversive bundle of useful helpers for contao.
This version will no longer add asset to your installation, do not dispatch curious caching events or similar.

The main changes are:
- All classic util classes and aliases are removed. Only the ones accessible via the `Utils` service are available.
- All deprecated services and functions are removed.
- Nearly all twig filters were dropped.
- No more bundled assets. You can install the asset component still as yarn dependency.

More specific changes, but not limited to:
- Changed: bundle class name is now `HeimrichHannotUtilsBundle`
- Changed: DcaUtil::getDcaFields() array options now throw error if not of type array
- Changed: RoutingUtil::generateBackendRoute() route argument moved to options array
- Removed: ContainerUtil::isBundleActive()
- Removed: UrlUtil::removeQueryStringParameterToUrl()
- Removed: a lot of not used dependencies

Changes since last beta version:
- Removed: AbstractServiceSubscriber (**potentially breaking!**)
- Fixed: compatibility with symfony 6 and contao 5.3
- Fixed: insert tag parsing

## [3.0.0-beta3] - 2024-01-10
Merge changes from 2.234.1:
- Fixed: missing title in entity finder block_module output

## [3.0.0-beta2] - 2024-01-09
Merge changes from 2.234.0: 
- Added: find*ByInserttag methods to EntityFinderHelper
- Changed: find articles by inserttags for html modules and content element in EntityFinder command

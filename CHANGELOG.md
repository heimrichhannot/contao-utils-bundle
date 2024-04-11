# Changelog

All notable changes to this project will be documented in this file.

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

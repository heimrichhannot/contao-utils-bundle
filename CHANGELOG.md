# Changelog

All notable changes to this project will be documented in this file.



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

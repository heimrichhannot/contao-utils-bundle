# Changelog

All notable changes to this project will be documented in this file.

## [3.0.0] - TBD
This version is a complete reworked version of utils bundle. 
The goal was to have a non-inversive bundle of useful helpers for contao.
This version will no longer add asset to your installation, do not dispatch curious caching events or similar.

The main changes are:
- All classic util classes and aliases are removed. Only the ones accessible via the `Utils` service are available.
- All deprecated services and functions are removed.
- Nearly all twig filters were dropped.
- No more bundled assets. You install the asset component still as yarn dependency.

More specific changes, but not limited to:
- Changed: bundle class name is now `HeimrichHannotUtilsBundle`
- Changed: DcaUtil::getDcaFields() array options now throw error if not of type array
- Removed: ContainerUtil::isBundleActive()
- Removed: UrlUtil::removeQueryStringParameterToUrl()
- Changed: RoutingUtil::generateBackendRoute() route argument moved to options array

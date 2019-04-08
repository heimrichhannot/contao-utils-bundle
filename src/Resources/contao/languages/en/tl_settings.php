<?php

$lang = &$GLOBALS['TL_LANG']['tl_settings'];

/**
 * Fields
 */
$lang['activateDbCache'][0]   = 'Activate database cache';
$lang['activateDbCache'][1]   = 'Choose this option to activate the database cache.';
$lang['dbCacheMaxTime'][0]    = 'Maximum cache time';
$lang['dbCacheMaxTime'][1]    = 'Type in the time period a value can persist in the cache.';
$lang['utilsGoogleApiKey'][0] = 'Google API key';
$lang['utilsGoogleApiKey'][1] = 'Type in your Google API key here. It\'s used to calculate coordinates.';

/**
 * Fields
 */
$lang['headerAddXFrame'][0]       = 'Add "X-Frame Header"';
$lang['headerAddXFrame'][1]       = 'Add "X-Frame-Options: SAMEORIGIN" to http header to protect against clickjacking';
$lang['headerXFrameSkipPages'][0] = 'Exclude "X-Frame Header" pages';
$lang['headerXFrameSkipPages'][1] = 'Do not add "X-Frame-Options: SAMEORIGIN" to http header on defined pages (for example iframe embed pages).';
$lang['headerAllowOrigins'][0]    = 'Add Access-Control-Allow-Origins Header';
$lang['headerAllowOrigins'][1]    = 'Add "Access-Control-Allow-Origins" to http header if current request url is present in current contao environment.';
$lang['hpProxy'][0]               = 'HTTP Proxy';
$lang['hpProxy'][1]               = 'Define an custom HTTP Proxy.';

/**
 * Legends
 */
$lang['utils_bundle_legend'] = 'Contao Utils Bundle';
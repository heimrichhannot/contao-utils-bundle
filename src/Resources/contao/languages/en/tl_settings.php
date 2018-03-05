<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2016 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_settings']['headerAddXFrame'][0]       = 'Add "X-Frame Header"';
$GLOBALS['TL_LANG']['tl_settings']['headerAddXFrame'][1]       = 'Add "X-Frame-Options: SAMEORIGIN" to http header to protect against clickjacking';
$GLOBALS['TL_LANG']['tl_settings']['headerXFrameSkipPages'][0] = 'Exclude "X-Frame Header" pages';
$GLOBALS['TL_LANG']['tl_settings']['headerXFrameSkipPages'][1] = 'Do not add "X-Frame-Options: SAMEORIGIN" to http header on defined pages (for example iframe embed pages).';
$GLOBALS['TL_LANG']['tl_settings']['headerAllowOrigins'][0]    = 'Add Access-Control-Allow-Origins Header';
$GLOBALS['TL_LANG']['tl_settings']['headerAllowOrigins'][1]    =
    'Add "Access-Control-Allow-Origins" to http header if current request url is present in current contao environment.';
$GLOBALS['TL_LANG']['tl_settings']['hpProxy'][0]               = 'HTTP Proxy';
$GLOBALS['TL_LANG']['tl_settings']['hpProxy'][1]               = 'Define an custom HTTP Proxy.';

$GLOBALS['TL_LANG']['tl_settings']['utils_bundle_legend'] = 'Utils-Bundle Settings';
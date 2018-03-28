<?php

$dca = &$GLOBALS['TL_DCA']['tl_settings'];

/**
 * Palettes
 */
$dca['palettes']['__selector__'][] = 'activateDbCache';
$dca['palettes']['default'] .= ';{db_cache_legend},activateDbCache;';

/**
 * Subpalettes
 */
$dca['subpalettes']['activateDbCache'] = 'dbCacheMaxTime';

/**
 * Fields
 */
$fields = [
    'activateDbCache' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['activateDbCache'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true]
    ],
    'dbCacheMaxTime'  => [
        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['dbCacheMaxTime'],
        'exclude'   => true,
        'inputType' => 'timePeriod',
        'options'   => ['m', 'h', 'd'],
        'reference' => &$GLOBALS['TL_LANG']['MSC']['timePeriod'],
        'eval'      => ['mandatory' => true, 'tl_class' => 'w50']
    ]
];

if (!\Contao\Config::get('dbCacheMaxTime'))
{
    \Contao\Config::set('dbCacheMaxTime', serialize(\HeimrichHannot\UtilsBundle\Cache\DatabaseCacheUtil::DEFAULT_MAX_CACHE_TIME));
    \Contao\Config::persist('dbCacheMaxTime', serialize(\HeimrichHannot\UtilsBundle\Cache\DatabaseCacheUtil::DEFAULT_MAX_CACHE_TIME));
}

$dca['fields'] = array_merge($fields, $dca['fields']);
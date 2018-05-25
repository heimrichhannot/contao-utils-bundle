<?php

/**
 * JS
 */
array_insert(
    $GLOBALS['TL_JAVASCRIPT'],
    1,
    [
        'contao-utils-bundle'        => 'bundles/heimrichhannotcontaoutils/js/utils-bundle.min.js|static',
        'contao-utils-bundle-arrays' => 'bundles/heimrichhannotcontaoutils/js/arrays.min.js|static',
        'contao-utils-bundle-url'    => 'bundles/heimrichhannotcontaoutils/js/url.min.js|static',
        'contao-utils-bundle-util'   => 'bundles/heimrichhannotcontaoutils/js/util.min.js|static'
    ]
);

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_cfg_tag'] = 'HeimrichHannot\UtilsBundle\Model\CfgTagModel';
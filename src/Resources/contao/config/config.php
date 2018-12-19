<?php

/**
 * JS
 */
array_insert(
    $GLOBALS['TL_JAVASCRIPT'],
    1,
    [
        'contao-utils-bundle'        => 'bundles/heimrichhannotcontaoutils/js/contao-utils-bundle.js|static'
    ]
);

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_cfg_tag'] = 'HeimrichHannot\UtilsBundle\Model\CfgTagModel';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['replaceInsertTags']['contao-utils-bundle'] = ['huh.utils.listener.insert_tags', 'onReplaceInsertTags'];
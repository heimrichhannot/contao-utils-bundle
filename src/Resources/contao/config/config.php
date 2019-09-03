<?php

/**
 * JS
 */
array_insert(
    $GLOBALS['TL_JAVASCRIPT'],
    1,
    [
        'contao-utils-bundle' => 'bundles/heimrichhannotcontaoutils/js/contao-utils-bundle.js|static'
    ]
);

/*
 * Assets
 */
if (System::getContainer()->get('huh.utils.container')->isBackend()) {
    $GLOBALS['TL_CSS']['utils-bundle'] = 'bundles/heimrichhannotcontaoutils/css/contao-utils-bundle.be.css|static';
}

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_cfg_tag'] = 'HeimrichHannot\UtilsBundle\Model\CfgTagModel';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['replaceInsertTags']['huh.utils.listener.insert_tags']    = ['huh.utils.listener.insert_tags', 'onReplaceInsertTags'];
$GLOBALS['TL_HOOKS']['initializeSystem']['huh.utils.template']                 = ['huh.utils.template', 'getAllTemplates'];
$GLOBALS['TL_HOOKS']['loadDataContainer']['huh.utils.tree_cache']              = ['huh.utils.cache.database_tree', 'loadDataContainer'];
$GLOBALS['TL_HOOKS']['modifyFrontendPage']['huh.utils.listener.frontend_page'] = ['huh.utils.listener.frontend_page', 'modifyFrontendPage'];
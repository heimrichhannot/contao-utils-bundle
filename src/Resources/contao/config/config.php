<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

/*
 * Models
 */
$GLOBALS['TL_MODELS']['tl_cfg_tag'] = 'HeimrichHannot\UtilsBundle\Model\CfgTagModel';

/*
 * Hooks
 */
$GLOBALS['TL_HOOKS']['replaceInsertTags']['huh.utils.listener.insert_tags'] = ['huh.utils.listener.insert_tags', 'onReplaceInsertTags'];
$GLOBALS['TL_HOOKS']['initializeSystem']['huh.utils.template'] = ['huh.utils.template', 'getAllTemplates'];
$GLOBALS['TL_HOOKS']['loadDataContainer']['huh.utils.tree_cache'] = ['huh.utils.cache.database_tree', 'loadDataContainer'];
$GLOBALS['TL_HOOKS']['modifyFrontendPage']['huh.utils.listener.frontend_page'] = ['huh.utils.listener.frontend_page', 'modifyFrontendPage'];

$GLOBALS['TL_HOOKS']['initializeSystem']['huh_utils'] = [\HeimrichHannot\UtilsBundle\EventListener\InitializeSystemListener::class, '__invoke'];

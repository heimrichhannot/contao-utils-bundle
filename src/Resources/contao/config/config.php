<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

/*
 * Models
 */

use HeimrichHannot\UtilsBundle\EventListener\InitializeSystemListener;
use HeimrichHannot\UtilsBundle\EventListener\InsertTagsListener;

/*
 * Hooks
 */
$GLOBALS['TL_HOOKS']['replaceInsertTags']['huh.utils.listener.insert_tags'] = [InsertTagsListener::class, 'onReplaceInsertTags'];
$GLOBALS['TL_HOOKS']['initializeSystem']['huh.utils.template'] = ['huh.utils.template', 'getAllTemplates'];
$GLOBALS['TL_HOOKS']['loadDataContainer']['huh.utils.tree_cache'] = ['huh.utils.cache.database_tree', 'loadDataContainer'];
$GLOBALS['TL_HOOKS']['initializeSystem']['huh_utils'] = [InitializeSystemListener::class, '__invoke'];

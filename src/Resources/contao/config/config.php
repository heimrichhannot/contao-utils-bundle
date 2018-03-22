<?php

/**
 * JS
 */
if (System::getContainer()->get('huh.utils.container')->isFrontend())
{
    array_insert(
        $GLOBALS['TL_JAVASCRIPT'],
        1,
        [
            'contao-utils-bundle'     => 'bundles/heimrichhannotcontaoutils/js/utils-bundle.min.js|static',
            'contao-utils-bundle-url' => 'bundles/heimrichhannotcontaoutils/js/url.min.js|static'
        ]
    );
}

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_cfg_tag'] = 'HeimrichHannot\UtilsBundle\Model\CfgTagModel';
<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\EventListener;

use HeimrichHannot\UtilsBundle\Container\ContainerUtil;

/**
 * Hook("initializeSystem").
 */
class InitializeSystemListener
{
    /**
     * @var ContainerUtil
     */
    protected $containerUtil;
    /**
     * @var array
     */
    protected $bundleConfig;

    /**
     * InitializeSystemListener constructor.
     */
    public function __construct(ContainerUtil $containerUtil, array $bundleConfig)
    {
        $this->containerUtil = $containerUtil;
        $this->bundleConfig = $bundleConfig;
    }

    public function __invoke(): void
    {
        if ($this->containerUtil->isBackend()) {
            $GLOBALS['TL_CSS']['utils-bundle'] = 'bundles/heimrichhannotcontaoutils/css/contao-utils-bundle.be.css|static';
        }

        if (isset($this->bundleConfig['enable_load_assets']) && true === $this->bundleConfig['enable_load_assets']) {
            array_insert($GLOBALS['TL_JAVASCRIPT'], 1, [
                'contao-utils-bundle' => 'bundles/heimrichhannotcontaoutils/js/contao-utils-bundle.js|static',
            ]);
        }
    }
}

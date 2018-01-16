<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\UtilsBundle\Container;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;

class ContainerUtil
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Returns the active bundles.
     *
     * @return array
     */
    public function getActiveBundles(): array
    {
        return System::getContainer()->getParameter('kernel.bundles');
    }

    /**
     * Checks if some bundle is active. Pass in the class name (e.g. 'HeimrichHannot\FilterBundle\HeimrichHannotContaoFilterBundle').
     *
     * @param $bundleName
     *
     * @return bool
     */
    public function isBundleActive($bundleName)
    {
        return in_array($bundleName, static::getActiveBundles(), true);
    }
}

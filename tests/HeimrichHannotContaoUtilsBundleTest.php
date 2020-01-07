<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests;

use HeimrichHannot\UtilsBundle\HeimrichHannotContaoUtilsBundle;
use PHPUnit\Framework\TestCase;

class HeimrichHannotContaoUtilsBundleTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $bundle = new HeimrichHannotContaoUtilsBundle();
        $this->assertInstanceOf(HeimrichHannotContaoUtilsBundle::class, $bundle);
    }
}

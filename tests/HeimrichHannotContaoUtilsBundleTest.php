<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests;

use HeimrichHannot\UtilsBundle\HeimrichHannotUtilsBundle;
use PHPUnit\Framework\TestCase;

class HeimrichHannotUtilsBundleTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $bundle = new HeimrichHannotUtilsBundle();
        $this->assertInstanceOf(HeimrichHannotUtilsBundle::class, $bundle);
    }
}

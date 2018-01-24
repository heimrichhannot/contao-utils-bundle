<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle;

use HeimrichHannot\UtilsBundle\DependencyInjection\UtilsExtension;
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

    /**
     * Tests the getContainerExtension() method.
     */
    public function testReturnsTheContainerExtension()
    {
        $bundle = new HeimrichHannotContaoUtilsBundle();
        $this->assertInstanceOf(
            UtilsExtension::class,
            $bundle->getContainerExtension()
        );
    }
}

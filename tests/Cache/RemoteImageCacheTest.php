<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Cache;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Cache\RemoteImageCache;

class RemoteImageCacheTest extends ContaoTestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $framework = $this->mockContaoFramework();
        $container = $this->mockContainer();
        $instance = new RemoteImageCache($framework, $container);
        $this->assertInstanceOf(RemoteImageCache::class, $instance);
    }
}

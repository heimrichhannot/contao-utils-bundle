<?php

/*
 * Copyright (c) 2026 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests;

use HeimrichHannot\UtilsBundle\HeimrichHannotUtilsBundle;
use HeimrichHannot\UtilsBundle\Util\RequestUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class HeimrichHannotUtilsBundleTest extends TestCase
{
    public function testLoadsServices(): void
    {
        $bundle = new HeimrichHannotUtilsBundle();
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');
        $container->setParameter('kernel.build_dir', sys_get_temp_dir());

        $bundle->getContainerExtension()->load([], $container);

        $this->assertSame(\dirname(__DIR__), $bundle->getPath());
        $this->assertTrue($container->getDefinition(Utils::class)->isPublic());
        $this->assertTrue($container->hasDefinition(RequestUtil::class));
    }
}

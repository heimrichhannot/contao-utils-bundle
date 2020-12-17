<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Util\Container\ContainerUtil;
use Symfony\Component\DependencyInjection\ServiceLocator;

class UtilsTest extends ContaoTestCase
{
    public function getTestInstance(array $parameter = [])
    {
        if (!isset($parameter['locator'])) {
            $parameter['locator'] = $this->createMock(ServiceLocator::class);
            $parameter['locator']->method('get')->willReturnCallback(function ($id) {
                switch ($id) {
                    case ContainerUtil::class:
                        return $this->createMock(ContainerUtil::class);
                }
            });
        }

        return new Utils($parameter['locator']);
    }

    public function testContainer()
    {
        $this->assertInstanceOf(ContainerUtil::class, $this->getTestInstance()->container());
    }

    public function testGetSubscribedServices()
    {
        $this->assertInternalType('array', $this->getTestInstance()::getSubscribedServices());
    }
}

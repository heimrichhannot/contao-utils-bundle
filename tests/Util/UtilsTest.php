<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Util;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Util\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Util\Locale\LocaleUtil;
use HeimrichHannot\UtilsBundle\Util\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Util\String\StringUtil;
use HeimrichHannot\UtilsBundle\Util\User\UserUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
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

                    case LocaleUtil::class:
                        return $this->createMock(LocaleUtil::class);

                    case ModelUtil::class:
                        return $this->createMock(ModelUtil::class);

                    case StringUtil::class:
                        return $this->createMock(StringUtil::class);

                    case UserUtil::class:
                        return $this->createMock(UserUtil::class);
                }

                return null;
            });
        }

        return new Utils($parameter['locator']);
    }

    public function testContainer()
    {
        $this->assertInstanceOf(ContainerUtil::class, $this->getTestInstance()->container());
    }

    public function testLocale()
    {
        $this->assertInstanceOf(LocaleUtil::class, $this->getTestInstance()->locale());
    }

    public function testModel()
    {
        $this->assertInstanceOf(ModelUtil::class, $this->getTestInstance()->model());
    }

    public function testString()
    {
        $this->assertInstanceOf(StringUtil::class, $this->getTestInstance()->string());
    }

    public function testUser()
    {
        $this->assertInstanceOf(UserUtil::class, $this->getTestInstance()->user());
    }

    public function testGetSubscribedServices()
    {
        $this->assertInternalType('array', $this->getTestInstance()::getSubscribedServices());
    }
}
<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Util;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Util\ArrayUtil;
use HeimrichHannot\UtilsBundle\Util\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Util\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Util\Locale\LocaleUtil;
use HeimrichHannot\UtilsBundle\Util\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Util\Request\RequestUtil;
use HeimrichHannot\UtilsBundle\Util\String\StringUtil;
use HeimrichHannot\UtilsBundle\Util\Ui\AccordionUtil;
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
                    case AccordionUtil::class:
                        return $this->createMock(AccordionUtil::class);

                    case ArrayUtil::class:
                        return $this->createMock(ArrayUtil::class);

                    case ContainerUtil::class:
                        return $this->createMock(ContainerUtil::class);

                    case DcaUtil::class:
                        return $this->createMock(DcaUtil::class);

                    case LocaleUtil::class:
                        return $this->createMock(LocaleUtil::class);

                    case ModelUtil::class:
                        return $this->createMock(ModelUtil::class);

                    case RequestUtil::class:
                        return $this->createMock(RequestUtil::class);

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

    public function testAccordion()
    {
        $this->assertInstanceOf(AccordionUtil::class, $this->getTestInstance()->accordion());
    }

    public function testArray()
    {
        $this->assertInstanceOf(ArrayUtil::class, $this->getTestInstance()->array());
    }

    public function testContainer()
    {
        $this->assertInstanceOf(ContainerUtil::class, $this->getTestInstance()->container());
    }

    public function testDca()
    {
        $this->assertInstanceOf(DcaUtil::class, $this->getTestInstance()->dca());
    }

    public function testLocale()
    {
        $this->assertInstanceOf(LocaleUtil::class, $this->getTestInstance()->locale());
    }

    public function testModel()
    {
        $this->assertInstanceOf(ModelUtil::class, $this->getTestInstance()->model());
    }

    public function testRequest()
    {
        $this->assertInstanceOf(RequestUtil::class, $this->getTestInstance()->request());
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
        $this->assertTrue(\is_array($this->getTestInstance()::getSubscribedServices()));
    }
}

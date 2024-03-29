<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Util;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Util\AccordionUtil;
use HeimrichHannot\UtilsBundle\Util\AnonymizeUtil;
use HeimrichHannot\UtilsBundle\Util\ArrayUtil;
use HeimrichHannot\UtilsBundle\Util\ClassUtil;
use HeimrichHannot\UtilsBundle\Util\ContainerUtil;
use HeimrichHannot\UtilsBundle\Util\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Util\DcaUtil;
use HeimrichHannot\UtilsBundle\Util\FileUtil;
use HeimrichHannot\UtilsBundle\Util\FormatterUtil;
use HeimrichHannot\UtilsBundle\Util\HtmlUtil;
use HeimrichHannot\UtilsBundle\Util\LocaleUtil;
use HeimrichHannot\UtilsBundle\Util\ModelUtil;
use HeimrichHannot\UtilsBundle\Util\RequestUtil;
use HeimrichHannot\UtilsBundle\Util\RoutingUtil;
use HeimrichHannot\UtilsBundle\Util\StringUtil;
use HeimrichHannot\UtilsBundle\Util\UrlUtil;
use HeimrichHannot\UtilsBundle\Util\UserUtil;
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

                    case AnonymizeUtil::class:
                        return $this->createMock(AnonymizeUtil::class);

                    case ArrayUtil::class:
                        return $this->createMock(ArrayUtil::class);

                    case ClassUtil::class:
                        return $this->createMock(ClassUtil::class);

                    case ContainerUtil::class:
                        return $this->createMock(ContainerUtil::class);

                    case DatabaseUtil::class:
                        return $this->createMock(DatabaseUtil::class);

                    case DcaUtil::class:
                        return $this->createMock(DcaUtil::class);

                    case FileUtil::class:
                        return $this->createMock(FileUtil::class);

                    case FormatterUtil::class:
                        return $this->createMock(FormatterUtil::class);

                    case HtmlUtil::class:
                        return $this->createMock(HtmlUtil::class);

                    case LocaleUtil::class:
                        return $this->createMock(LocaleUtil::class);

                    case ModelUtil::class:
                        return $this->createMock(ModelUtil::class);

                    case RequestUtil::class:
                        return $this->createMock(RequestUtil::class);

                    case RoutingUtil::class:
                        return $this->createMock(RoutingUtil::class);

                    case StringUtil::class:
                        return $this->createMock(StringUtil::class);

                    case UrlUtil::class:
                        return $this->createMock(UrlUtil::class);

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
    public function testAnonymize()
    {
        $this->assertInstanceOf(AnonymizeUtil::class, $this->getTestInstance()->anonymize());
    }

    public function testArray()
    {
        $this->assertInstanceOf(ArrayUtil::class, $this->getTestInstance()->array());
    }

    public function testContainer()
    {
        $this->assertInstanceOf(ContainerUtil::class, $this->getTestInstance()->container());
    }

    public function testClass()
    {
        $this->assertInstanceOf(ClassUtil::class, $this->getTestInstance()->class());
    }

    public function testDatabase()
    {
        $this->assertInstanceOf(DatabaseUtil::class, $this->getTestInstance()->database());
    }

    public function testFile()
    {
        $this->assertInstanceOf(FileUtil::class, $this->getTestInstance()->file());
    }

    public function testFormatter()
    {
        $this->assertInstanceOf(FormatterUtil::class, $this->getTestInstance()->formatter());
    }

    public function testDca()
    {
        $this->assertInstanceOf(DcaUtil::class, $this->getTestInstance()->dca());
    }

    public function testHtml()
    {
        $this->assertInstanceOf(HtmlUtil::class, $this->getTestInstance()->html());
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

    public function testRouting()
    {
        $this->assertInstanceOf(RoutingUtil::class, $this->getTestInstance()->routing());
    }

    public function testString()
    {
        $this->assertInstanceOf(StringUtil::class, $this->getTestInstance()->string());
    }

    public function testUrl()
    {
        $this->assertInstanceOf(UrlUtil::class, $this->getTestInstance()->url());
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

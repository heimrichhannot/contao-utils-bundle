<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Cache;

use Contao\Config;
use Contao\Database;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Cache\DatabaseCacheUtil;
use HeimrichHannot\UtilsBundle\Date\DateUtil;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DatabaseCacheUtilTest extends ContaoTestCase
{
    public function getDatabaseCacheUtilMock(ContainerBuilder $container = null)
    {
        if (!$container) {
            $container = $this->mockContainer();
        }
        $framework = $this->mockContaoFramework();
        $framework->method('createInstance')->willReturnCallback(function ($class) {
            switch ($class) {
                case Database::class:
                    $db = $this->mockAdapter(['prepare', 'execute']);
                    $db->method('prepare')->willReturnSelf();
                    $db->method('execute')->willReturnCallback(function () {
                        $arrParams = \func_get_args();

                        if (!$arrParams || empty($arrParams)) {
                            throw new \Exception('No parameter set!');
                        }

                        switch ($arrParams[0]) {
                            case 'zero':
                                $resultSetMock = $this->mockClassWithProperties(Database\Result::class, [
                                    'numRows' => 0,
                                ]);

                                return $resultSetMock;

                            case 'one':
                                $resultSetMock = $this->mockClassWithProperties(Database\Result::class, [
                                    'numRows' => 1,
                                    'cacheValue' => '1',
                                ]);

                                return $resultSetMock;

                            default:
                                if (\count($arrParams) > 1) {
                                    $this->assertTrue(is_numeric($arrParams[0]));
                                    $this->assertTrue(is_numeric($arrParams[1]));
                                    $this->assertTrue(\is_string($arrParams[2]));
                                    $this->assertTrue(\is_string($arrParams[3]));
                                }
                                $resultSetMock = $this->mockClassWithProperties(Database\Result::class, [
                                    'numRows' => 0,
                                ]);

                                return $resultSetMock;
                        }
                    });

                    return $db;
            }
        });
        $container->set('contao.framework', $framework);
        $dateUtilMock = new DateUtil($container);
        $container->set('huh.utils.date', $dateUtilMock);

        return new DatabaseCacheUtil($container);
    }

    public function testKeyExists()
    {
        $util = $this->getDatabaseCacheUtilMock();
        $this->assertFalse($util->keyExists('zero'));
        $this->assertTrue($util->keyExists('one'));
    }

    public function testGetValue()
    {
        $util = $this->getDatabaseCacheUtilMock();

        Config::set('activateDbCache', false);
        $this->assertFalse($util->getValue('hello'));

        Config::set('activateDbCache', true);
        $this->assertFalse($util->getValue('zero'));
        $this->assertSame('1', $util->getValue('one'));
    }

    public function testCacheValue()
    {
        $util = $this->getDatabaseCacheUtilMock();

        Config::set('activateDbCache', false);
        $this->assertFalse($util->cacheValue('hello', 'world'));

        Config::set('activateDbCache', true);
        $this->assertTrue($util->cacheValue('newkey', 'newvalue'));
    }

    public function testCacheValueException()
    {
        $util = $this->getDatabaseCacheUtilMock();
        Config::set('activateDbCache', true);

        $this->expectException(\Exception::class);

        $this->assertTrue($util->cacheValue('one', 'value'));
    }
}

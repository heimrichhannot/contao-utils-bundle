<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Database;

use Contao\Database;
use Contao\System;
use HeimrichHannot\UtilsBundle\Arrays\ArrayUtil;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Tests\TestCaseEnvironment;

class DatabaseUtilTest extends TestCaseEnvironment
{
    public function setUp()
    {
        parent::setUp();

        $container = System::getContainer();
        $arrayUtils = new ArrayUtil($this->mockContaoFramework());
        $container->set('huh.utils.array', $arrayUtils);
        System::setContainer($container);
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $framework = $this->mockContaoFramework();
        $instance = new DatabaseUtil($framework);
        $this->assertInstanceOf('HeimrichHannot\UtilsBundle\Database\DatabaseUtil', $instance);
    }

    public function testProcessInPieces()
    {
        $countQuery = 'SELECT COUNT(*) as total FROM lawyers';
        $query = 'SELECT * FROM lawyers';

        // perfect run
        $total = $this->mockClassWithProperties(Database\Result::class, ['total' => 10]);
        $result = $this->mockClassWithProperties(Database\Result::class, ['numRows' => 10]);
        $result->method('fetchAssoc')->willReturn(['row' => 10], false);
        $databaseAdapter = $this->mockAdapter(['execute', 'prepare', 'limit']);
        $databaseAdapter->method('execute')->willReturn($total);
        $databaseAdapter->method('prepare')->willReturn($databaseAdapter);
        $limitAdapter = $this->mockAdapter(['execute']);
        $limitAdapter->method('execute')->willReturn($result);
        $databaseAdapter->method('limit')->willReturn($limitAdapter);
        $databaseUtil = new DatabaseUtil($this->mockContaoFramework([Database::class => $databaseAdapter]));

        $result = $databaseUtil->processInPieces($countQuery, $query, 'array_keys', 'row');
        $this->assertSame(10, $result);

        // total < 1
        $total = $this->mockClassWithProperties(Database\Result::class, ['total' => 0]);
        $databaseAdapter = $this->mockAdapter(['execute', 'prepare', 'limit']);
        $databaseAdapter->method('execute')->willReturn($total);

        $databaseUtil = new DatabaseUtil($this->mockContaoFramework([Database::class => $databaseAdapter]));
        $result = $databaseUtil->processInPieces($countQuery, $query, 'array_keys');
        $this->assertFalse($result);

        // numRows < 1
        $total = $this->mockClassWithProperties(Database\Result::class, ['total' => 10]);
        $result = $this->mockClassWithProperties(Database\Result::class, ['numRows' => 0]);
        $databaseAdapter = $this->mockAdapter(['execute', 'prepare', 'limit']);
        $databaseAdapter->method('execute')->willReturn($total);
        $databaseAdapter->method('prepare')->willReturn($databaseAdapter);
        $limitAdapter = $this->mockAdapter(['execute']);
        $limitAdapter->method('execute')->willReturn($result);
        $databaseAdapter->method('limit')->willReturn($limitAdapter);
        $databaseUtil = new DatabaseUtil($this->mockContaoFramework([Database::class => $databaseAdapter]));

        $result = $databaseUtil->processInPieces($countQuery, $query, 'array_keys', 'row');
        $this->assertFalse($result);

        // return [] = $row
        $total = $this->mockClassWithProperties(Database\Result::class, ['total' => 10]);
        $result = $this->mockClassWithProperties(Database\Result::class, ['numRows' => 10]);
        $result->method('fetchAssoc')->willReturn(['row' => 10], false);
        $databaseAdapter = $this->mockAdapter(['execute', 'prepare', 'limit']);
        $databaseAdapter->method('execute')->willReturn($total);
        $databaseAdapter->method('prepare')->willReturn($databaseAdapter);
        $limitAdapter = $this->mockAdapter(['execute']);
        $limitAdapter->method('execute')->willReturn($result);
        $databaseAdapter->method('limit')->willReturn($limitAdapter);
        $databaseUtil = new DatabaseUtil($this->mockContaoFramework([Database::class => $databaseAdapter]));

        $result = $databaseUtil->processInPieces($countQuery, $query, 'is_array');
        $this->assertSame(10, $result);

        // stupid input handling
        $total = $this->mockClassWithProperties(Database\Result::class, ['total' => 10]);
        $result = $this->mockClassWithProperties(Database\Result::class, ['numRows' => 10]);
        $result->method('fetchAssoc')->willReturn(['row' => 10], false);
        $databaseAdapter = $this->mockAdapter(['execute', 'prepare', 'limit']);
        $databaseAdapter->method('execute')->willReturn($total);
        $databaseAdapter->method('prepare')->willReturn($databaseAdapter);
        $limitAdapter = $this->mockAdapter(['execute']);
        $limitAdapter->method('execute')->willReturn($result);
        $databaseAdapter->method('limit')->willReturn($limitAdapter);
        $databaseUtil = new DatabaseUtil($this->mockContaoFramework([Database::class => $databaseAdapter]));

        $result = $databaseUtil->processInPieces($countQuery, $query, null, null, -10);
        $this->assertSame(10, $result);

        $result = $databaseUtil->processInPieces($countQuery, $query, 'is_array', 12);
        $this->assertSame(10, $result);
    }

    public function testDoBulkInsert()
    {
        $databaseAdapter = $this->mockAdapter(['tableExists', 'getFieldNames', 'execute', 'prepare']);
        $databaseAdapter->method('tableExists')->willReturn(true);
        $databaseAdapter->method('getFieldNames')->willReturn(['id', 'name', 'date']);
        $databaseAdapter->method('prepare')->willReturn($databaseAdapter);

        // return null
        $databaseUtil = new DatabaseUtil($this->mockContaoFramework([Database::class => $databaseAdapter]));
        $result = $databaseUtil->doBulkInsert('table', []);
        $this->assertNull($result);

        // perfect run
        $result = $databaseUtil->doBulkInsert('table', [['name' => 'DEFAULT', 'date' => time()], ['name' => 'max', 'date' => time()]], [], 'UPDATE');
        $this->assertNull($result);
    }
}

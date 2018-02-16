<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Database;

use Contao\Database;
use Contao\Model;
use Contao\System;
use HeimrichHannot\UtilsBundle\Arrays\ArrayUtil;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Tests\TestCaseEnvironment;

class DatabaseUtilTest extends TestCaseEnvironment
{
    public function setUp()
    {
        parent::setUp();

        if (!defined('TL_ROOT')) {
            \define('TL_ROOT', '');
        }

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
        $model = $this->createMock(Model::class);
        $model->method('row')->willReturn(['name' => 'max', 'date' => time()]);

        $data = [['name' => 'DEFAULT', 'date' => time()], ['name' => 'max', 'date' => time()], $model];

        $result = $databaseUtil->doBulkInsert('table', $data, ['name' => 'Max'], 'UPDATE', 'is_array', function ($return, $fields, $varData) { return null; }, 2);
        $this->assertNull($result);

        $result = $databaseUtil->doBulkInsert('table', $data, ['name' => 'Max'], 'UPDATE', 'is_array', function ($return, $fields, $varData) { return $varData; }, 2);
        $this->assertNull($result);

        // names != name
        $data = [['names' => 'DEFAULT', 'dates' => time()]];
        $result = $databaseUtil->doBulkInsert('table', $data, ['name' => 'Max'], 'UPDATE', 'is_array', function ($return, $fields, $varData) { return $varData; }, -1);
        $this->assertNull($result);
    }

    public function testCreateWhereForSerializeBlob()
    {
        $databaseUtil = new DatabaseUtil($this->mockContaoFramework());

        // wrong connective
        try {
            $result = $databaseUtil->createWhereForSerializedBlob('field', [], 'blaa fu');
        } catch (\Exception $exception) {
            $this->assertSame('Unknown sql junctor', $exception->getMessage());
        }

        // perfect run
        $result = $databaseUtil->createWhereForSerializedBlob('field', ['value1', 'value2']);
        $this->assertCount(2, $result);
        $this->assertSame('(field REGEXP (?) OR field REGEXP (?))', $result[0]);
        $this->assertCount(2, $result[1]);
    }

    public function testTransformVerboseOperator()
    {
        $databaseUtil = new DatabaseUtil($this->mockContaoFramework());

        $result = $databaseUtil->transformVerboseOperator('like');
        $this->assertSame('LIKE', $result);
        $result = $databaseUtil->transformVerboseOperator('unlike');
        $this->assertSame('NOT LIKE', $result);
        $result = $databaseUtil->transformVerboseOperator('equal');
        $this->assertSame('=', $result);
        $result = $databaseUtil->transformVerboseOperator('unequal');
        $this->assertSame('!=', $result);
        $result = $databaseUtil->transformVerboseOperator('lower');
        $this->assertSame('<', $result);
        $result = $databaseUtil->transformVerboseOperator('greater');
        $this->assertSame('>', $result);
        $result = $databaseUtil->transformVerboseOperator('lowerequal');
        $this->assertSame('<=', $result);
        $result = $databaseUtil->transformVerboseOperator('greaterequal');
        $this->assertSame('>=', $result);
        $result = $databaseUtil->transformVerboseOperator('in');
        $this->assertSame('IN', $result);
        $result = $databaseUtil->transformVerboseOperator('notin');
        $this->assertSame('NOT IN', $result);
        $result = $databaseUtil->transformVerboseOperator('isnull');
        $this->assertSame('NOT IN', $result);
        $result = $databaseUtil->transformVerboseOperator('isnotnull');
        $this->assertSame('IS NOT NULL', $result);
        $result = $databaseUtil->transformVerboseOperator('blaa');
        $this->assertFalse($result);
    }

    public function testComputeCondition()
    {
        $databaseUtil = new DatabaseUtil($this->mockContaoFramework());

        $GLOBALS['TL_DCA']['table']['fields']['field'] = ['sql' => 'blob'];

        // perfect run
        $result = $databaseUtil->computeCondition('field', 'like', 'value', 'table');
        $this->assertSame(['field LIKE ?', ['%"value"%']], $result);
        $this->assertCount(2, $result);

        $result = $databaseUtil->computeCondition('field', 'equal', 'value');
        $this->assertSame(['field = ?', ['value']], $result);
        $this->assertCount(2, $result);

        $result = $databaseUtil->computeCondition('field', 'unequal', 'value');
        $this->assertSame(['field != ?', ['value']], $result);
        $this->assertCount(2, $result);

        $result = $databaseUtil->computeCondition('field', 'lower', 'value');
        $this->assertSame(['field < CAST(? AS DECIMAL)', ['value']], $result);
        $this->assertCount(2, $result);

        $result = $databaseUtil->computeCondition('field', 'greater', 'value');
        $this->assertSame(['field > CAST(? AS DECIMAL)', ['value']], $result);
        $this->assertCount(2, $result);

        $result = $databaseUtil->computeCondition('field', 'lowerequal', 'value');
        $this->assertSame(['field <= CAST(? AS DECIMAL)', ['value']], $result);
        $this->assertCount(2, $result);

        $result = $databaseUtil->computeCondition('field', 'greaterequal', 'value');
        $this->assertSame(['field >= CAST(? AS DECIMAL)', ['value']], $result);
        $this->assertCount(2, $result);

        $result = $databaseUtil->computeCondition('field', 'in', 'value');
        $this->assertSame(["field IN ('value')", []], $result);
        $this->assertCount(2, $result);

        $result = $databaseUtil->computeCondition('field', 'notin', 'value');
        $this->assertSame(["field NOT IN ('value')", []], $result);
        $this->assertCount(2, $result);

        $result = $databaseUtil->computeCondition('field', 'isnull', 'value');
        $this->assertSame(['field NOT IN ', []], $result);
        $this->assertCount(2, $result);

        $result = $databaseUtil->computeCondition('field', 'isnotnull', 'value');
        $this->assertSame(['field IS NOT NULL ', []], $result);
        $this->assertCount(2, $result);

        // error handling
        $result = $databaseUtil->computeCondition('field', 'like', ['value'], 'table');
        $this->assertSame(['field LIKE ?', ['%"value"%']], $result);
        $this->assertCount(2, $result);
    }
}

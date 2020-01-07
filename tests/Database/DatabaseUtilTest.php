<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
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

        if (!\defined('TL_ROOT')) {
            \define('TL_ROOT', sys_get_temp_dir().\DIRECTORY_SEPARATOR);
        }

        $container = System::getContainer();
        $container->setParameter('contao.image.target_dir', TL_ROOT.'/data');
        $arrayUtils = new ArrayUtil($container);
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
        $framework = $this->mockContaoFramework();
        $framework->method('createInstance')->willReturn($databaseAdapter);
        $databaseUtil = new DatabaseUtil($framework);

        $result = $databaseUtil->processInPieces($countQuery, $query, 'array_keys', 'row');
        $this->assertSame(10, $result);

        // total < 1
        $total = $this->mockClassWithProperties(Database\Result::class, ['total' => 0]);
        $databaseAdapter = $this->mockAdapter(['execute', 'prepare', 'limit']);
        $databaseAdapter->method('execute')->willReturn($total);

        $framework = $this->mockContaoFramework();
        $framework->method('createInstance')->willReturn($databaseAdapter);
        $databaseUtil = new DatabaseUtil($framework);
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
        $framework = $this->mockContaoFramework();
        $framework->method('createInstance')->willReturn($databaseAdapter);
        $databaseUtil = new DatabaseUtil($framework);

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
        $framework = $this->mockContaoFramework();
        $framework->method('createInstance')->willReturn($databaseAdapter);
        $databaseUtil = new DatabaseUtil($framework);

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
        $framework = $this->mockContaoFramework();
        $framework->method('createInstance')->willReturn($databaseAdapter);
        $databaseUtil = new DatabaseUtil($framework);

        $result = $databaseUtil->processInPieces($countQuery, $query, null, null, -10);
        $this->assertSame(10, $result);

        $result = $databaseUtil->processInPieces($countQuery, $query, 'is_array', 12);
        $this->assertSame(10, $result);
    }

    public function testDoBulkInsert()
    {
        $databaseAdapter = $this->mockAdapter(['tableExists', 'getFieldNames', 'execute', 'prepare']);
        $databaseAdapter->method('tableExists')->willReturn(true);
        $databaseAdapter->method('getFieldNames')->willReturn(['id', 'name', 'date', 'test']);
        $databaseAdapter->method('prepare')->willReturnSelf();

        // return null
        $framework = $this->mockContaoFramework();
        $framework->method('createInstance')->willReturn($databaseAdapter);
        $databaseUtil = new DatabaseUtil($framework);
        $result = $databaseUtil->doBulkInsert('table', []);
        $this->assertNull($result);

        // perfect run
        $model = $this->createMock(Model::class);
        $model->method('row')->willReturn(['name' => 'max', 'date' => time()]);

        $data = [['name' => 'DEFAULT', 'date' => time(), 'test' => 'DEFAULT'], ['name' => 'max', 'date' => time()], $model];

        $result = $databaseUtil->doBulkInsert('table', $data, ['name' => 'Max'], 'UPDATE', 'is_array', function ($return, $fields, $varData) { return null; }, 2);
        $this->assertNull($result);

        $result = $databaseUtil->doBulkInsert('table', $data, ['name' => 'Max'], 'UPDATE', 'is_array', function ($return, $fields, $varData) { return $varData; }, 2);
        $this->assertNull($result);

        $result = $databaseUtil->doBulkInsert('table', $data, ['test' => 'DEFAULT', 'name' => 'DEFAULT', 'date' => 'DEFAULT'], 'UPDATE', 'is_array', function ($return, $fields, $varData) { return $varData; }, 2);
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
        $this->assertSame(['table.field LIKE ?', ['%"value"%']], $result);
        $this->assertCount(2, $result);

        $result = $databaseUtil->computeCondition('field', 'unlike', 'value');
        $this->assertSame(['field NOT LIKE ?', ['%value%']], $result);
        $this->assertCount(2, $result);

        $result = $databaseUtil->computeCondition('field', 'unlike', ['value']);
        $this->assertSame(['field NOT LIKE ?', ['%value%']], $result);
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
        $this->assertSame(['field IN ("value")', []], $result);
        $this->assertCount(2, $result);

        $result = $databaseUtil->computeCondition('field', 'notin', 'value');
        $this->assertSame(['field NOT IN ("value")', []], $result);
        $this->assertCount(2, $result);

        $result = $databaseUtil->computeCondition('field', 'isnull', 'value');
        $this->assertSame(['field NOT IN ', []], $result);
        $this->assertCount(2, $result);

        $result = $databaseUtil->computeCondition('field', 'isnotnull', 'value');
        $this->assertSame(['field IS NOT NULL ', []], $result);
        $this->assertCount(2, $result);

        // error handling
        $result = $databaseUtil->computeCondition('field', 'like', ['value'], 'table');
        $this->assertSame(['table.field LIKE ?', ['%"value"%']], $result);
        $this->assertCount(2, $result);
    }

    public function testComposeWhereForQueryBuilder()
    {
        $databaseUtil = new DatabaseUtil($this->mockContaoFramework());

        // perfect run
        $result = $databaseUtil->composeWhereForQueryBuilder($this->getQueryBuilderMock(), 'field', 'like', ['eval' => ['multiple' => []]], null);
        $this->assertSame('LIKE', $result);
        $result = $databaseUtil->composeWhereForQueryBuilder($this->getQueryBuilderMock(), 'field', 'unlike', ['eval' => ['multiple' => []]], null);
        $this->assertSame('NOT LIKE', $result);
        $result = $databaseUtil->composeWhereForQueryBuilder($this->getQueryBuilderMock(), 'field', 'in', ['eval' => ['multiple' => []]], [1, 2]);
        $this->assertSame('IN', $result);
        $result = $databaseUtil->composeWhereForQueryBuilder($this->getQueryBuilderMock(), 'field', 'notin', ['eval' => ['multiple' => []]], [1, 2]);
        $this->assertSame('NOT IN', $result);
        $result = $databaseUtil->composeWhereForQueryBuilder($this->getQueryBuilderMock(), 'field', 'isnull', ['eval' => ['multiple' => []]], null);
        $this->assertSame('IS NULL', $result);
        $result = $databaseUtil->composeWhereForQueryBuilder($this->getQueryBuilderMock(), 'field', 'isnotnull', ['eval' => ['multiple' => []]], null);
        $this->assertSame('IS NOT NULL', $result);
        $result = $databaseUtil->composeWhereForQueryBuilder($this->getQueryBuilderMock(), 'field', 'greaterequal', ['eval' => ['multiple' => []]], null);
        $this->assertSame('>=', $result);
        $result = $databaseUtil->composeWhereForQueryBuilder($this->getQueryBuilderMock(), 'field', 'greater', ['eval' => ['multiple' => []]], null);
        $this->assertSame('>', $result);
        $result = $databaseUtil->composeWhereForQueryBuilder($this->getQueryBuilderMock(), 'field', 'lowerequal', ['eval' => ['multiple' => []]], null);
        $this->assertSame('<=', $result);
        $result = $databaseUtil->composeWhereForQueryBuilder($this->getQueryBuilderMock(), 'field', 'lower', ['eval' => ['multiple' => []]], null);
        $this->assertSame('<', $result);
        $result = $databaseUtil->composeWhereForQueryBuilder($this->getQueryBuilderMock(), 'field', 'unequal', ['eval' => ['multiple' => []]], null);
        $this->assertSame('<>', $result);
        $result = $databaseUtil->composeWhereForQueryBuilder($this->getQueryBuilderMock(), 'field', 'equal', ['eval' => ['multiple' => []]], null);
        $this->assertSame('=', $result);
        $result = $databaseUtil->composeWhereForQueryBuilder($this->getQueryBuilderMock(), 'field', 'regexp', ['eval' => ['multiple' => []]], null);
        $this->assertSame('field REGEXP :field', $result);
        $result = $databaseUtil->composeWhereForQueryBuilder($this->getQueryBuilderMock(), 'field', 'regexp', ['eval' => ['multiple' => true]], ['array']);
        $this->assertSame('field REGEXP :field', $result);
        $result = $databaseUtil->composeWhereForQueryBuilder($this->getQueryBuilderMock(), 'field', 'regexp', ['eval' => ['multiple' => true]], 'array');
        $this->assertSame('field REGEXP :field', $result);
        $result = $databaseUtil->composeWhereForQueryBuilder($this->getQueryBuilderMock(), 'field', 'boo', ['eval' => ['multiple' => true]], 'array');
        $this->assertSame('', $result);
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getQueryBuilderMock()
    {
        $mock = $this->getMockBuilder(\Doctrine\DBAL\Query\QueryBuilder::class)->disableOriginalConstructor()->setMethods([
            'expr',
            'like',
            'notLike',
            'in',
            'notIn',
            'isNull',
            'isNotNull',
            'gte',
            'gt',
            'lte',
            'lt',
            'neq',
            'eq',
            'setParameter',
        ])->getMock();
        $mock->expects($this->any())->method('expr')->willReturnSelf();
        $mock->expects($this->any())->method('like')->willReturn('LIKE');
        $mock->expects($this->any())->method('notLike')->willReturn('NOT LIKE');
        $mock->expects($this->any())->method('in')->willReturn('IN');
        $mock->expects($this->any())->method('notIn')->willReturn('NOT IN');
        $mock->expects($this->any())->method('isNull')->willReturn('IS NULL');
        $mock->expects($this->any())->method('isNotNull')->willReturn('IS NOT NULL');
        $mock->expects($this->any())->method('gte')->willReturn('>=');
        $mock->expects($this->any())->method('gt')->willReturn('>');
        $mock->expects($this->any())->method('lte')->willReturn('<=');
        $mock->expects($this->any())->method('lt')->willReturn('<');
        $mock->expects($this->any())->method('neq')->willReturn('<>');
        $mock->expects($this->any())->method('eq')->willReturn('=');
        $mock->expects($this->any())->method('setParameter');

        return $mock;
    }
}

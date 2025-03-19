<?php

namespace HeimrichHannot\UtilsBundle\Tests\EntityFinder;

use Contao\ContentModel;
use Contao\Model;
use Contao\ModuleModel;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use HeimrichHannot\UtilsBundle\EntityFinder\EntityFinderHelper;
use HeimrichHannot\UtilsBundle\Tests\AbstractUtilsTestCase;
use HeimrichHannot\UtilsBundle\Util\DatabaseUtil\CreateWhereForSerializedBlobResult;
use HeimrichHannot\UtilsBundle\Util\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use PHPUnit\Framework\MockObject\MockBuilder;
use function Clue\StreamFilter\fun;

class EntityFinderHelperTest extends AbstractUtilsTestCase
{
    public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null)
    {
        return new EntityFinderHelper(
            $parameters['utils'] ?? $this->createMock(Utils::class),
            $parameters['framework'] ?? $this->createMockContaoFramework(),
            $parameters['connection'] ?? $this->createMock(Connection::class)
        );
    }

    public function testFindModulesByTypeAndSerializedValue()
    {
        $moduleModel = $this->mockAdapter(['findBy']);
        $moduleModel->expects($this->once())->method('findBy')->willReturn(null);
        $framework = $this->mockContaoFramework([
            ModuleModel::class => $moduleModel,
        ]);

        $databaseUtilMock = $this->createMock(DatabaseUtil::class);
        $databaseUtilMock->method('createWhereForSerializedBlob')->willReturn(
            new CreateWhereForSerializedBlobResult('field', [])
        );
        $utils = $this->createMock(Utils::class);
        $utils->method('database')->willReturn($databaseUtilMock);

        $instance = $this->getTestInstance([
            'utils' => $utils,
            'framework' => $framework,
        ]);

        $this->assertNull($instance->findModulesByTypeAndSerializedValue('newslist', 'news_archives', [3]));
    }

    public function testFetchModelOrData()
    {
        $contentAdapter = $this->mockAdapter(['findByIdOrAlias']);
        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->willReturnCallback(function (string $table) {
            return match ($table) {
                'tl_content' => ContentModel::class,
                default => 'SomeNonExistingModel',
            };
        });
        $framework = $this->mockContaoFramework([
            ContentModel::class => $contentAdapter,
            Model::class => $modelAdapter,
        ]);
        $instance = $this->getTestInstance([
            'framework' => $framework,
        ]);
        $model = $instance->fetchModelOrData('tl_content', 1);
        $this->assertNull($model);

        $contentAdapter = $this->mockAdapter(['findByIdOrAlias']);
        $contentAdapter->method('findByIdOrAlias')->willReturn($this->mockModelObject(ContentModel::class));
        $framework = $this->mockContaoFramework([
            ContentModel::class => $contentAdapter,
            Model::class => $modelAdapter,
        ]);
        $instance = $this->getTestInstance([
            'framework' => $framework,
        ]);
        $model = $instance->fetchModelOrData('tl_content', 1);
        $this->assertInstanceOf(ContentModel::class, $model);

        // Test with non existing model

        $connection = $this->createMock(Connection::class);
        $schema = $this->createMock(AbstractSchemaManager::class);
        $schema->method('tablesExist')->willReturn(false);
        $connection->method('createSchemaManager')->willReturn($schema);
        $instance = $this->getTestInstance([
            'framework' => $framework,
            'connection' => $connection,
        ]);
        $model = $instance->fetchModelOrData('tl_example', 5);
        $this->assertNull($model);

        $connection = $this->createMock(Connection::class);
        $schema = $this->createMock(AbstractSchemaManager::class);
        $schema->method('tablesExist')->willReturn(true);
        $connection->method('createSchemaManager')->willReturn($schema);
        $resultNull = $this->createMock(Result::class);
        $resultNull->method('rowCount')->willReturn(0);
        $resultSuccess = $this->createMock(Result::class);
        $resultSuccess->method('rowCount')->willReturn(1);
        $resultSuccess->method('fetchAssociative')->willReturn(['id' => 5]);
        $connection->method('executeQuery')
            ->willReturnOnConsecutiveCalls($resultNull, $resultSuccess, $resultNull, $resultSuccess);
        $instance = $this->getTestInstance([
            'framework' => $framework,
            'connection' => $connection,
        ]);

        $this->assertNull($instance->fetchModelOrData('tl_example', 'alias'));
        $this->assertInstanceOf(
            Model::class,
            $instance->fetchModelOrData('tl_example', 'alias')
        );
        $this->assertNull($instance->fetchModelOrData('tl_example', 5));
        $this->assertInstanceOf(
            Model::class,
            $instance->fetchModelOrData('tl_example', 5)
        );



    }


}

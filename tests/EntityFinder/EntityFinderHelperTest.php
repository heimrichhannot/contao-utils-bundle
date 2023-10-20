<?php

namespace HeimrichHannot\UtilsBundle\Tests\EntityFinder;

use Contao\ModuleModel;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\EntityFinder\EntityFinderHelper;
use HeimrichHannot\UtilsBundle\Util\DatabaseUtil\CreateWhereForSerializedBlobResult;
use HeimrichHannot\UtilsBundle\Util\DatabaseUtil\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;

class EntityFinderHelperTest extends ContaoTestCase
{
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

        $instance = new EntityFinderHelper($utils, $framework);

        $this->assertNull($instance->findModulesByTypeAndSerializedValue('newslist', 'news_archives', [3]));
    }
}

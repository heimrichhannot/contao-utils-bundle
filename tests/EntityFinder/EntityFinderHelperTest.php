<?php

namespace HeimrichHannot\UtilsBundle\Tests\EntityFinder;

use Contao\ModuleModel;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\EntityFinder\EntityFinderHelper;
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

        $utils = $this->createMock(Utils::class);
        $instance = new EntityFinderHelper($utils, $framework);

        $instance->findModulesByTypeAndSerializedValue('newslist', 'news_archives', [3]);
    }
}

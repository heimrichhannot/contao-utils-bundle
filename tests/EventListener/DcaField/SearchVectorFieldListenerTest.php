<?php

namespace HeimrichHannot\UtilsBundle\Tests\EventListener\DcaField;

use Contao\DataContainer;
use HeimrichHannot\UtilsBundle\Dca\SearchVectorField;
use HeimrichHannot\UtilsBundle\EventListener\DcaField\SearchVectorFieldListener;
use HeimrichHannot\UtilsBundle\SearchVector\SearchVectorIndexer;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class SearchVectorFieldListenerTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testOnLoadDataContainer(): void
    {
        $listener = new SearchVectorFieldListener($this->createMock(ContainerInterface::class));
        $table = 'test_table';
        $GLOBALS['TL_DCA'][$table] = ['config' => [], 'fields' => []];

        $listener->onLoadDataContainer($table);

        $this->assertSame(['config' => [], 'fields' => []], $GLOBALS['TL_DCA'][$table]);

        SearchVectorField::register($table)->setFields(['title']);
        $listener->onLoadDataContainer($table);

        $this->assertSame(
            [
                'eval' => ['doNotShow' => true],
                'sql' => ['type' => 'text', 'notnull' => false],
                'exclude' => true,
            ],
            $GLOBALS['TL_DCA'][$table]['fields']['searchVector']
        );
        $this->assertSame([[SearchVectorFieldListener::class, 'onSubmitCallback']], $GLOBALS['TL_DCA'][$table]['config']['onsubmit_callback']);
        $this->assertSame([[SearchVectorFieldListener::class, 'onCopyCallback']], $GLOBALS['TL_DCA'][$table]['config']['oncopy_callback']);
        $this->assertSame([[SearchVectorFieldListener::class, 'onRestoreVersionCallback']], $GLOBALS['TL_DCA'][$table]['config']['onrestore_version_callback']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testCustomFieldName(): void
    {
        $listener = new SearchVectorFieldListener($this->createMock(ContainerInterface::class));
        $table = 'test_table';
        $GLOBALS['TL_DCA'][$table] = ['config' => [], 'fields' => []];

        SearchVectorField::register($table)
            ->setFieldName('app_searchVector')
            ->setExclude(false)
            ->setEvalValue('tl_class', 'clr');

        $listener->onLoadDataContainer($table);

        $this->assertArrayNotHasKey('searchVector', $GLOBALS['TL_DCA'][$table]['fields']);
        $this->assertSame(
            ['doNotShow' => true, 'tl_class' => 'clr'],
            $GLOBALS['TL_DCA'][$table]['fields']['app_searchVector']['eval']
        );
        $this->assertArrayNotHasKey('exclude', $GLOBALS['TL_DCA'][$table]['fields']['app_searchVector']);
    }

    public function testCallbacksIndexTheRecord(): void
    {
        $dc = $this->createMock(DataContainer::class);
        $dc->method('__get')->willReturnMap([['id', 3], ['table', 'tl_example']]);

        $calls = [];
        $indexer = $this->createMock(SearchVectorIndexer::class);
        $indexer->expects($this->exactly(3))->method('index')->willReturnCallback(
            static function (string $table, int $id, ?DataContainer $dc = null) use (&$calls): bool {
                $calls[] = [$table, $id, $dc];

                return true;
            }
        );

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->with(SearchVectorIndexer::class)->willReturn($indexer);

        $listener = new SearchVectorFieldListener($container);
        $listener->onSubmitCallback($dc);
        $listener->onCopyCallback(7, $dc);
        $listener->onRestoreVersionCallback('tl_other', '12', 4, []);

        $this->assertSame([['tl_example', 3, $dc], ['tl_example', 7, null], ['tl_other', 12, null]], $calls);
    }

    public function testSubmitWithoutIdIsIgnored(): void
    {
        $dc = $this->createMock(DataContainer::class);
        $dc->method('__get')->willReturnMap([['id', null], ['table', 'tl_example']]);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->never())->method('get');

        (new SearchVectorFieldListener($container))->onSubmitCallback($dc);
    }
}

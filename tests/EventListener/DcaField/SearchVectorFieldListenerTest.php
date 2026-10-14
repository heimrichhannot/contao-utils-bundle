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

    public function testSeveralFieldsInOneDcaAreRejected(): void
    {
        $listener = new SearchVectorFieldListener($this->createMock(ContainerInterface::class));
        $GLOBALS['TL_DCA']['tl_listener_twice'] = ['config' => [], 'fields' => []];

        SearchVectorField::register('tl_listener_twice')->setFieldName('searchTitle');
        SearchVectorField::register('tl_listener_twice')->setFieldName('searchVector');

        try {
            $this->expectException(\LogicException::class);
            $this->expectExceptionMessage('Table "tl_listener_twice" registers 2 search vector fields (searchTitle, searchVector), but only one search vector field per table is supported.');

            $listener->onLoadDataContainer('tl_listener_twice');
        } finally {
            unset($GLOBALS['TL_DCA']['tl_listener_twice']);
        }
    }

    public function testReloadingTheDcaRegistersTheFieldAgain(): void
    {
        $listener = new SearchVectorFieldListener($this->createMock(ContainerInterface::class));

        // Loading the DCA twice in one process, e.g. without DCA cache or after a framework reset
        foreach ([1, 2] as $load) {
            $GLOBALS['TL_DCA']['tl_listener_reload'] = ['config' => [], 'fields' => []];
            SearchVectorField::register('tl_listener_reload')->setFieldName('searchVector');

            $listener->onLoadDataContainer('tl_listener_reload');

            $this->assertArrayHasKey('searchVector', $GLOBALS['TL_DCA']['tl_listener_reload']['fields'], 'Load ' . $load);
        }

        unset($GLOBALS['TL_DCA']['tl_listener_reload']);
    }

    public function testCallbacksIndexTheRecordRightAwayIfNotAsync(): void
    {
        SearchVectorField::register('tl_listener_sync')->setAsync(false);
        SearchVectorField::register('tl_listener_sync_other')->setAsync(false);

        $dc = $this->createMock(DataContainer::class);
        $dc->method('__get')->willReturnMap([['id', 3], ['table', 'tl_listener_sync']]);

        $calls = [];
        $indexer = $this->createMock(SearchVectorIndexer::class);
        $indexer->expects($this->never())->method('queue');
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
        $listener->onRestoreVersionCallback('tl_listener_sync_other', '12', 4, []);

        $this->assertSame([['tl_listener_sync', 3, $dc], ['tl_listener_sync', 7, null], ['tl_listener_sync_other', 12, null]], $calls);
    }

    public function testCallbacksQueueTheRecordByDefault(): void
    {
        SearchVectorField::register('tl_listener_async');

        $dc = $this->createMock(DataContainer::class);
        $dc->method('__get')->willReturnMap([['id', 3], ['table', 'tl_listener_async']]);

        $calls = [];
        $indexer = $this->createMock(SearchVectorIndexer::class);
        $indexer->expects($this->never())->method('index');
        $indexer->expects($this->exactly(3))->method('queue')->willReturnCallback(
            static function (string $table, int $id) use (&$calls): void {
                $calls[] = [$table, $id];
            }
        );

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->with(SearchVectorIndexer::class)->willReturn($indexer);

        $listener = new SearchVectorFieldListener($container);
        $listener->onSubmitCallback($dc);
        $listener->onCopyCallback(7, $dc);
        $listener->onRestoreVersionCallback('tl_listener_async', '12', 4, []);

        $this->assertSame([['tl_listener_async', 3], ['tl_listener_async', 7], ['tl_listener_async', 12]], $calls);
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

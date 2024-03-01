<?php

namespace HeimrichHannot\UtilsBundle\Tests\EventListener\DcaField;

use Contao\DataContainer;
use Contao\Model;
use HeimrichHannot\TestUtilitiesBundle\Mock\ModelMockTrait;
use HeimrichHannot\UtilsBundle\Dca\DateAddedField;
use HeimrichHannot\UtilsBundle\EventListener\DcaField\DateAddedFieldListener;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class DateAddedFieldListenerTest extends TestCase
{
    use ModelMockTrait;

    /**
     * @runInSeparateProcess
     */
    public function testOnLoadDataContainer()
    {
        $container = $this->createMock(ContainerInterface::class);

        $listener = new DateAddedFieldListener($container);
        $table = 'test_table';

        // Mock the global array
        $GLOBALS['TL_DCA'][$table] = [
            'config' => [],
            'fields' => [],
        ];

        $listener->onLoadDataContainer($table);

        $this->assertArrayNotHasKey('onload_callback', $GLOBALS['TL_DCA'][$table]['config']);
        $this->assertArrayNotHasKey('oncopy_callback', $GLOBALS['TL_DCA'][$table]['config']);
        $this->assertArrayNotHasKey('dateAdded', $GLOBALS['TL_DCA'][$table]['fields']);

        DateAddedField::register($table);

        $listener->onLoadDataContainer($table);

        $this->assertArrayHasKey('onload_callback', $GLOBALS['TL_DCA'][$table]['config']);
        $this->assertArrayHasKey('oncopy_callback', $GLOBALS['TL_DCA'][$table]['config']);
        $this->assertArrayHasKey('dateAdded', $GLOBALS['TL_DCA'][$table]['fields']);
    }

    public function testOnLoadCallback()
    {
        // Without DataContainer

        $listener = $this->getMockBuilder(DateAddedFieldListener::class)
            ->setConstructorArgs([$this->createMock(ContainerInterface::class)])
            ->onlyMethods(['getModelInstance'])
            ->getMock();

        $listener->expects($this->never())->method('getModelInstance');

        $listener->onLoadCallback();

        // With DataContainer, but no model instance

        $listener = $this->getMockBuilder(DateAddedFieldListener::class)
            ->setConstructorArgs([$this->createMock(ContainerInterface::class)])
            ->onlyMethods(['getModelInstance'])
            ->getMock();

        $dc = $this->getMockBuilder(DataContainer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dc->id = 1;
        $dc->method('__get')->willReturnCallback(function ($name) {
            switch ($name) {
                case 'table':
                    return 'test_table';
                case 'id':
                    return 1;
            }
            return null;
        });

        $listener->method('getModelInstance')->willReturn(null);

        $listener->onLoadCallback($dc);

        // Complete run

        $listener = $this->getMockBuilder(DateAddedFieldListener::class)
            ->setConstructorArgs([$this->createMock(ContainerInterface::class)])
            ->onlyMethods(['getModelInstance'])
            ->getMock();

        $model = $this->mockModelObject(Model::class);

        $model->dateAdded = 0;

        $listener->method('getModelInstance')->willReturn($model);

        $listener->onLoadCallback($dc);

        $this->assertGreaterThan(0, $model->dateAdded);
    }

    public function testOnCopyCallback()
    {
        $listener = $this->getMockBuilder(DateAddedFieldListener::class)
            ->setConstructorArgs([$this->createMock(ContainerInterface::class)])
            ->onlyMethods(['getModelInstance'])
            ->getMock();

        $dc = $this->getMockBuilder(DataContainer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dc->method('__get')->willReturnCallback(function ($name) {
            switch ($name) {
                case 'table':
                    return 'test_table';
            }
            return null;
        });

        $listener->expects($this->never())->method('getModelInstance');

        $listener->onCopyCallback(1, $dc);

        // Run without model

        $dc = $this->getMockBuilder(DataContainer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dc->method('__get')->willReturnCallback(function ($name) {
            switch ($name) {
                case 'table':
                    return 'test_table';
                case 'id':
                    return 1;
            }
        });

        $listener = $this->getMockBuilder(DateAddedFieldListener::class)
            ->setConstructorArgs([$this->createMock(ContainerInterface::class)])
            ->onlyMethods(['getModelInstance'])
            ->getMock();

        $listener->method('getModelInstance')->willReturn(null);

        $listener->onCopyCallback(1, $dc);

        // Complete run

        $listener = $this->getMockBuilder(DateAddedFieldListener::class)
            ->setConstructorArgs([$this->createMock(ContainerInterface::class)])
            ->onlyMethods(['getModelInstance'])
            ->getMock();

        $dc = $this->getMockBuilder(DataContainer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dc->method('__get')->willReturnCallback(function ($name) {
            switch ($name) {
                case 'table':
                    return 'test_table';
                case 'id':
                    return 1;
            }
        });

        $model = $this->mockModelObject(Model::class);

        $model->dateAdded = 0;

        $listener->method('getModelInstance')->willReturn($model);

        $listener->onCopyCallback(1, $dc);

        $this->assertGreaterThan(0, $model->dateAdded);
    }
}
<?php

namespace HeimrichHannot\UtilsBundle\Tests\EntityFinder;

use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DC_Table;
use Contao\Model;
use Contao\Model\Collection;
use Contao\ModuleModel;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use HeimrichHannot\TestUtilitiesBundle\Mock\ModelMockTrait;
use HeimrichHannot\UtilsBundle\EntityFinder\Element;
use HeimrichHannot\UtilsBundle\EntityFinder\EntityFinderHelper;
use HeimrichHannot\UtilsBundle\EntityFinder\Finder;
use HeimrichHannot\UtilsBundle\Event\EntityFinderFindEvent;
use HeimrichHannot\UtilsBundle\Tests\AbstractUtilsTestCase;
use PHPUnit\Framework\MockObject\MockBuilder;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class FinderTest extends AbstractUtilsTestCase
{
    use ModelMockTrait;

    /**
     * @param array{
     *     helper?: EntityFinderHelper,
     *     eventDispatcher?: EventDispatcherInterface,
     *     framework?: ContaoFramework,
     *     connection?: Connection
     * } $parameters
     */
    public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null)
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->method('dispatch')->willReturnArgument(0);

        $contaoFramework = $this->mockContaoFramework([
            Controller::class => $this->mockAdapter(['loadDataContainer']),
        ]);

        $result = $this->createMock(Result::class);
        $result->method('fetchAssociative')->willReturn([]);

        $connection = $this->createMock(Connection::class);
        $connection->method('executeQuery')->willReturn($result);
        return new Finder(
            $parameters['helper'] ?? $this->createMock(EntityFinderHelper::class),
            $parameters['eventDispatcher'] ?? $eventDispatcher,
            $parameters['framework'] ?? $contaoFramework,
            $parameters['connection'] ?? $connection,
        );
    }

    public function testFindEmpty()
    {
        $finder = $this->getTestInstance();
        $this->assertNull($finder->find('find', 1));
        $this->assertNull($finder->find('tl_custom', 1));
    }

    public function testFindEvent()
    {
        $element = new Element(3, 'tl_custom', 'Custom');
        $event = new EntityFinderFindEvent('tl_custom', 3);
        $event->setElement($element);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->method('dispatch')->willReturn($event);
        $finder = $this->getTestInstance([
            'eventDispatcher' => $eventDispatcher
        ]);
        $this->assertSame($finder->find('tl_custom', 3), $element);
    }

    public function testFindFallback()
    {
        $element = $this->mockModelObject(Model::class, [
            'id' => 1,
            'pid' => 3,
        ]);

        $helper = $this->createMock(EntityFinderHelper::class);
        $helper->method('fetchModelOrData')->willReturn($element);
        $finder = $this->getTestInstance(['helper' => $helper]);
        $entity = $finder->find('tl_custom', 1);
        $this->assertInstanceOf(Element::class, $entity);
        $this->assertSame('tl_custom', $entity->table);
        $this->assertSame(1, $entity->id);
        $this->assertNull($entity->getParents());
        $this->assertNull($entity->description);

        $GLOBALS['TL_DCA']['tl_custom'] = [
            'config' => [
                'dataContainer' => DC_Table::class,
                'ptable' => 'tl_parent',
            ],
        ];

        $entity = $finder->find('tl_custom', 1);
        $this->assertInstanceOf(Element::class, $entity);
        $this->assertSame('tl_custom', $entity->table);
        $this->assertSame(1, $entity->id);
        $this->assertInstanceOf(\Generator::class, $entity->getParents());
        $this->assertNull($entity->description);
        $this->assertSame([['table' => 'tl_parent', 'id' => 3]], iterator_to_array($entity->getParents()));
    }

    public function testFindFallbackDynamicPtable()
    {
        $GLOBALS['TL_DCA']['tl_custom'] = [
            'config' => [
                'dataContainer' => DC_Table::class,
                'dynamicPtable' => true,
            ],
            'fields' => [
                'pid' => [],
                'ptable' => [],
            ]
        ];

        $element = $this->mockModelObject(Model::class, [
            'id' => 1,
            'pid' => 4,
            'ptable' => 'tl_other_parent',
        ]);

        $helper = $this->createMock(EntityFinderHelper::class);
        $helper->method('fetchModelOrData')->willReturn($element);
        $finder = $this->getTestInstance(['helper' => $helper]);

        $entity = $finder->find('tl_custom', 1);
        $this->assertInstanceOf(Element::class, $entity);
        $this->assertSame('tl_custom', $entity->table);
        $this->assertSame(1, $entity->id);
        $this->assertInstanceOf(\Generator::class, $entity->getParents());
        $this->assertNull($entity->description);
        $this->assertSame([['table' => 'tl_other_parent', 'id' => 4]], iterator_to_array($entity->getParents()));
    }

    public function testFindForm()
    {
        $moduleModel = $this->mockAdapter(['findByForm']);
        $contentModel = $this->mockAdapter(['findByForm']);
        $framework = $this->mockContaoFramework([
            ModuleModel::class => $moduleModel,
            ContentModel::class => $contentModel,
        ]);

        $finder = $this->getTestInstance([
            'framework' => $framework
        ]);
        $entity = $finder->find('tl_form', 1);
        $this->assertNull($entity);

        $element = $this->mockModelObject(Model::class, [
            'id' => 1,
            'pid' => 4,
            'title' => 'Test',
        ]);

        $helper = $this->createMock(EntityFinderHelper::class);
        $helper->method('fetchModelOrData')->willReturn($element);
        $finder = $this->getTestInstance([
            'helper' => $helper,
            'framework' => $framework
        ]);

        $entity = $finder->find('tl_form', 1);
        $this->assertInstanceOf(Element::class, $entity);
        $this->assertSame('tl_form', $entity->table);
        $this->assertSame(1, $entity->id);
        $this->assertSame('Form Test (ID: 1)', $entity->description);
        $this->assertInstanceOf(\Generator::class, $entity->getParents());
        $this->assertCount(0, iterator_to_array($entity->getParents()));


        $moduleModel = $this->mockAdapter(['findByForm']);
        $moduleModel->method('findByForm')->willReturn(
            new Collection([$this->mockModelObject(ModuleModel::class, ['id' => 4])], 'tl_module')
        );
        $contentModel = $this->mockAdapter(['findByForm']);
        $contentModel->method('findByForm')->willReturn(
            new Collection([$this->mockModelObject(ContentModel::class, ['id' => 5])], 'tl_content')
        );
        $framework = $this->mockContaoFramework([
            ModuleModel::class => $moduleModel,
            ContentModel::class => $contentModel,
        ]);

        $helper = $this->createMock(EntityFinderHelper::class);
        $helper->method('fetchModelOrData')->willReturn($element);
        $helper->method('findModulesByInserttag')->willReturn(
            [$this->mockModelObject(ModuleModel::class, ['id' => 6])]
        );
        $helper->method('findContentElementByInserttag')->willReturn(
            [$this->createModelDummyInstance(ContentModel::getTable(), ['id' => 7])]
        );

        $finder = $this->getTestInstance([
            'helper' => $helper,
            'framework' => $framework
        ]);
        $entity = $finder->find('tl_form', 1);
        $parents = iterator_to_array($entity->getParents());
        $this->assertCount(4, $parents);
        $this->assertSame('tl_module', $parents[0]['table']);
        $this->assertSame(4, $parents[0]['id']);
        $this->assertSame('tl_content', $parents[1]['table']);
        $this->assertSame(5, $parents[1]['id']);
        $this->assertSame('tl_module', $parents[2]['table']);
        $this->assertSame(6, $parents[2]['id']);
        $this->assertSame('tl_content', $parents[3]['table']);
        $this->assertSame(7, $parents[3]['id']);
    }

    public function testFindFormField()
    {
        $finder = $this->getTestInstance();
        $entity = $finder->find('tl_form_field', 1);
        $this->assertNull($entity);

        $element = $this->mockModelObject(Model::class, [
            'id' => 2,
            'pid' => 5,
            'name' => 'Field',
        ]);

        $helper = $this->createMock(EntityFinderHelper::class);
        $helper->method('fetchModelOrData')->willReturn($element);
        $finder = $this->getTestInstance(['helper' => $helper]);

        $entity = $finder->find('tl_form_field', 1);
        $this->assertInstanceOf(Element::class, $entity);
        $this->assertSame('tl_form_field', $entity->table);
        $this->assertSame(2, $entity->id);
        $this->assertSame('Form field Field (ID: 2)', $entity->description);
        $this->assertSame('Form field Field (ID: 2)', $entity->description);
        $this->assertInstanceOf(\Generator::class, $entity->getParents());
        $this->assertSame([['table' => 'tl_form', 'id' => 5]], iterator_to_array($entity->getParents()));
    }

    public function testFindModule()
    {
        $finder = $this->getTestInstance();
        $entity = $finder->find('tl_form', 1);
        $this->assertNull($entity);

        $element = $this->mockModelObject(Model::class, ['id' => 7, 'pid' => 2,]);
        $helper = $this->createMock(EntityFinderHelper::class);
        $helper->method('fetchModelOrData')->willReturn($element);

        $framework = $this->mockContaoFramework([
            ContentModel::class => $this->mockAdapter(['findBy']),
        ]);

        $finder = $this->getTestInstance([
            'helper' => $helper,
            'framework' => $framework,
        ]);

        $element = $finder->find('tl_module', 7);
        $this->assertInstanceOf(Element::class, $element);
        $this->assertSame('tl_module', $element->table);
        $this->assertSame(7, $element->id);

        $parents = iterator_to_array($element->getParents());
        $this->assertCount(1, $parents);
        $this->assertSame('tl_theme', $parents[0]['table']);
        $this->assertSame(2, $parents[0]['id']);

    }

    public function testFind()
    {
        $this->typicalFind('tl_list_config', 3, 'List config', function (Element $entity) {
            $this->assertSame([['table' => 'tl_module', 'id' => 4]], iterator_to_array($entity->getParents()));
        });
        $this->typicalFind('tl_list_config_element', 4, 'List config element', function (Element $entity) {
            $this->assertSame([['table' => 'tl_list_config', 'id' => 4]], iterator_to_array($entity->getParents()));
        });
    }

    private function typicalFind(string $table, int $id, string $name = 'Default', ?callable $callback = null)
    {
        $moduleModel = $this->mockAdapter(['findBy']);
        $moduleModel->method('findBy')->willReturnCallback(function ($columns, $values) {

            return new Collection([
                $this->mockModelObject(ModuleModel::class, [
                    'id' => 4
                ])
            ], 'tl_module');
        });
        $framework = $this->mockContaoFramework([
            ModuleModel::class => $moduleModel,
        ]);

        $finder = $this->getTestInstance([
            'framework' => $framework
        ]);
        $entity = $finder->find($table, $id);
        $this->assertNull($entity);

        $element = $this->mockModelObject(Model::class, [
            'id' => $id,
            'pid' => 4,
            'title' => $name,
            'name' => $name,
        ]);

        $helper = $this->createMock(EntityFinderHelper::class);
        $helper->method('fetchModelOrData')->willReturn($element);
        $finder = $this->getTestInstance([
            'helper' => $helper,
            'framework' => $framework
        ]);

        $entity = $finder->find($table, $id);
        $this->assertInstanceOf(Element::class, $entity);
        $this->assertSame($table, $entity->table);
        $this->assertSame($id, $entity->id);
        $this->assertInstanceOf(\Generator::class, $entity->getParents());

        if ($callback) {
            $callback($entity);
        }
    }
}
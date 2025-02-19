<?php

namespace HeimrichHannot\UtilsBundle\Tests\EntityFinder;

use Contao\Controller;
use Contao\DC_Table;
use Contao\Model\Collection;
use Contao\ModuleModel;
use HeimrichHannot\UtilsBundle\EntityFinder\Element;
use HeimrichHannot\UtilsBundle\EntityFinder\EntityFinderHelper;
use HeimrichHannot\UtilsBundle\EntityFinder\Finder;
use HeimrichHannot\UtilsBundle\Tests\AbstractUtilsTestCase;
use PHPUnit\Framework\MockObject\MockBuilder;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class FinderTest extends AbstractUtilsTestCase
{

    public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null)
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->method('dispatch')->willReturnArgument(0);

        $contaoFramework = $this->mockContaoFramework([
            Controller::class => $this->mockAdapter(['loadDataContainer']),
        ]);

        return new Finder(
            $parameters['helper'] ?? $this->createMock(EntityFinderHelper::class),
            $parameters['eventDispatcher'] ?? $eventDispatcher,
            $parameters['framework'] ?? $contaoFramework
        );
    }

    public function testFindEmpty()
    {
        $finder = $this->getTestInstance();
        $this->assertNull($finder->find('find', 1));
        $this->assertNull($finder->find('tl_custom', 1));
    }

    public function testFindFallback()
    {
        $element = new \stdClass();
        $element->id = 1;
        $element->pid = 3;

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

        $element = new \stdClass();
        $element->id = 1;
        $element->pid = 4;
        $element->ptable = 'tl_other_parent';

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
        $finder = $this->getTestInstance();
        $entity = $finder->find('tl_form', 1);
        $this->assertNull($entity);

        $element = new \stdClass();
        $element->id = 1;
        $element->pid = 4;
        $element->title = 'Test';

        $helper = $this->createMock(EntityFinderHelper::class);
        $helper->method('fetchModelOrData')->willReturn($element);
        $finder = $this->getTestInstance(['helper' => $helper]);

        $entity = $finder->find('tl_form', 1);
        $this->assertInstanceOf(Element::class, $entity);
        $this->assertSame('tl_form', $entity->table);
        $this->assertSame(1, $entity->id);
        $this->assertSame('Form Test (ID: 1)', $entity->description);
        $this->assertInstanceOf(\Generator::class, $entity->getParents());
    }

    public function testFindFormField()
    {
        $finder = $this->getTestInstance();
        $entity = $finder->find('tl_form_field', 1);
        $this->assertNull($entity);

        $element = new \stdClass();
        $element->id = 2;
        $element->pid = 5;
        $element->name = 'Field';

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

        $element = new \stdClass();
        $element->id = $id;
        $element->pid = 4;
        $element->title = $name;
        $element->name = $name;

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
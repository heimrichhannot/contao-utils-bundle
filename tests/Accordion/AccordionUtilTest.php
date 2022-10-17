<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Accordion;

use Contao\ContentModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Accordion\AccordionUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Tests\ModelMockTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AccordionUtilTest extends ContaoTestCase
{
    use ModelMockTrait;

    public function testCanBeInstantiated()
    {
        $instance = new AccordionUtil($this->getContainerMock());
        $this->assertInstanceOf(AccordionUtil::class, $instance);
    }

    public function testStructureAccordionSingle()
    {
        $container = $this->getContainerMock();
        $modelUtilMock = $this->createMock(ModelUtil::class);
        $modelUtilMock->method('findModelInstancesBy')->willReturnCallback(function (string $table, array $columns, array $values, array $options = []) {
            $pid = $values[1];

            switch ($pid) {
                case 1: return null;

                case 2:
                    return [
                        $this->mockModelObject(ContentModel::class, ['id' => 1, 'type' => 'accordionSingle']),
                    ];

                case 3:
                    return [
                        $this->mockModelObject(ContentModel::class, ['id' => 1, 'type' => 'accordionSingle']),
                        $this->mockModelObject(ContentModel::class, ['id' => 2, 'type' => 'text']),
                        $this->mockModelObject(ContentModel::class, ['id' => 3, 'type' => 'accordionSingle']),
                    ];

                case 4:
                    return [
                        $this->mockModelObject(ContentModel::class, ['id' => 1, 'type' => 'accordionSingle']),
                        $this->mockModelObject(ContentModel::class, ['id' => 2, 'type' => 'accordionSingle']),
                        $this->mockModelObject(ContentModel::class, ['id' => 3, 'type' => 'text']),
                    ];
            }

            return null;
        });
        $container->set('huh.utils.model', $modelUtilMock);

        $accordionUtil = new AccordionUtil($container);
        $data = [];
        $accordionUtil->structureAccordionSingle($data);
        $this->assertEmpty($data);

        $data = ['pid' => 1];
        $accordionUtil->structureAccordionSingle($data);
        $this->assertSame(['pid' => 1], $data);

        $data = ['id' => 1];
        $accordionUtil->structureAccordionSingle($data);
        $this->assertSame(['id' => 1], $data);

        $data = ['id' => 1, 'pid' => 1];
        $accordionUtil->structureAccordionSingle($data);
        $this->assertSame(['id' => 1, 'pid' => 1], $data);

        $data = ['id' => 1, 'pid' => 2, 'ptable' => 'tl_article'];
        $accordionUtil->structureAccordionSingle($data);
        $this->assertArrayHasKey('accordion_parentId', $data);
        $this->assertArrayHasKey('accordion_first', $data);
        $this->assertArrayHasKey('accordion_last', $data);
        $this->assertSame(1, $data['accordion_parentId']);
        $this->assertTrue($data['accordion_first']);
        $this->assertTrue($data['accordion_last']);

        $data = ['id' => 1, 'pid' => 3, 'ptable' => 'tl_article'];
        $accordionUtil->structureAccordionSingle($data);
        $this->assertArrayHasKey('accordion_parentId', $data);
        $this->assertArrayHasKey('accordion_first', $data);
        $this->assertArrayHasKey('accordion_last', $data);
        $this->assertSame(1, $data['accordion_parentId']);
        $this->assertTrue($data['accordion_first']);
        $this->assertTrue($data['accordion_last']);

        $data = ['id' => 1, 'pid' => 4, 'ptable' => 'tl_article'];
        $accordionUtil->structureAccordionSingle($data);
        $this->assertArrayHasKey('accordion_parentId', $data);
        $this->assertArrayHasKey('accordion_first', $data);
        $this->assertSame(1, $data['accordion_parentId']);
        $this->assertTrue($data['accordion_first']);
        $this->assertArrayNotHasKey('accordion_last', $data);

        $data = ['id' => 2, 'pid' => 4, 'ptable' => 'tl_article'];
        $accordionUtil->structureAccordionSingle($data);
        $this->assertArrayHasKey('accordion_parentId', $data);
        $this->assertArrayHasKey('accordion_last', $data);
        $this->assertSame(1, $data['accordion_parentId']);
        $this->assertTrue($data['accordion_last']);
        $this->assertArrayNotHasKey('accordion_first', $data);

        $data = ['id' => 2, 'pid' => 4, 'ptable' => 'tl_article'];
        $accordionUtil->structureAccordionSingle($data, 'card_');
        $this->assertArrayHasKey('card_parentId', $data);
        $this->assertArrayHasKey('card_last', $data);
        $this->assertSame(1, $data['card_parentId']);
        $this->assertTrue($data['card_last']);
        $this->assertArrayNotHasKey('card_first', $data);

        $container = $this->getContainerMock();
        $modelUtilMock = $this->createMock(ModelUtil::class);
        $modelUtilMock->expects($this->once())->method('findModelInstancesBy')->willReturnCallback(function (string $table, array $columns, array $values, array $options = []) {
            $pid = $values[1];

            switch ($pid) {
                case 4:
                    return [
                        $this->mockModelObject(ContentModel::class, ['id' => 1, 'type' => 'accordionSingle']),
                        $this->mockModelObject(ContentModel::class, ['id' => 2, 'type' => 'accordionSingle']),
                        $this->mockModelObject(ContentModel::class, ['id' => 3, 'type' => 'text']),
                    ];
            }

            return null;
        });
        $container->set('huh.utils.model', $modelUtilMock);

        $accordionUtil = new AccordionUtil($container);

        $data = ['id' => 2, 'pid' => 4, 'ptable' => 'tl_article'];
        $accordionUtil->structureAccordionSingle($data);
        $this->assertArrayHasKey('accordion_parentId', $data);
        $this->assertArrayHasKey('accordion_last', $data);
        $this->assertSame(1, $data['accordion_parentId']);
        $this->assertTrue($data['accordion_last']);
        $this->assertArrayNotHasKey('accordion_first', $data);

        $data = ['id' => 2, 'pid' => 4, 'ptable' => 'tl_article'];
        $accordionUtil->structureAccordionSingle($data);
        $this->assertArrayHasKey('accordion_parentId', $data);
        $this->assertArrayHasKey('accordion_last', $data);
        $this->assertSame(1, $data['accordion_parentId']);
        $this->assertTrue($data['accordion_last']);
        $this->assertArrayNotHasKey('accordion_first', $data);
    }

    /**
     * @param ContaoFramework $framework
     *
     * @return ContainerBuilder|ContainerInterface
     */
    protected function getContainerMock(ContainerBuilder $container = null, $framework = null)
    {
        if (!$container) {
            $container = $this->getContainerWithContaoConfiguration();
        }

        if (!$framework) {
            $framework = $this->mockContaoFramework();
        }
        $container->set('contao.framework', $framework);

        $modelUtil = $this->createMock(ModelUtil::class);
        $container->set('huh.utils.model', $modelUtil);

        return $container;
    }
}

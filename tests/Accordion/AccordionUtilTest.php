<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
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

        $data = ['id' => 1, 'pid' => 2];
        $accordionUtil->structureAccordionSingle($data);
        $this->assertArraySubset(['accordion_parentId' => 1, 'accordion_first' => true, 'accordion_last' => true], $data);

        $data = ['id' => 1, 'pid' => 3];
        $accordionUtil->structureAccordionSingle($data);
        $this->assertArraySubset(['accordion_parentId' => 1, 'accordion_first' => true, 'accordion_last' => true], $data);

        $data = ['id' => 1, 'pid' => 4];
        $accordionUtil->structureAccordionSingle($data);
        $this->assertArraySubset(['accordion_parentId' => 1, 'accordion_first' => true], $data);
        $this->assertArrayNotHasKey('accordion_last', $data);

        $data = ['id' => 2, 'pid' => 4];
        $accordionUtil->structureAccordionSingle($data);
        $this->assertArraySubset(['accordion_parentId' => 1, 'accordion_last' => true], $data);
        $this->assertArrayNotHasKey('accordion_first', $data);

        $data = ['id' => 2, 'pid' => 4];
        $accordionUtil->structureAccordionSingle($data, 'card_');
        $this->assertArraySubset(['card_parentId' => 1, 'card_last' => true], $data);
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

        $data = ['id' => 2, 'pid' => 4];
        $accordionUtil->structureAccordionSingle($data);
        $this->assertArraySubset(['accordion_parentId' => 1, 'accordion_last' => true], $data);
        $this->assertArrayNotHasKey('accordion_first', $data);

        $data = ['id' => 2, 'pid' => 4];
        $accordionUtil->structureAccordionSingle($data);
        $this->assertArraySubset(['accordion_parentId' => 1, 'accordion_last' => true], $data);
        $this->assertArrayNotHasKey('accordion_first', $data);
    }

    public function testStructureAccordionStartStop()
    {
        $container = $this->getContainerMock();
        $modelUtilMock = $this->createMock(ModelUtil::class);
        $modelUtilMock->method('findModelInstancesBy')->willReturnCallback(function (string $table, array $columns, array $values, array $options = []) {
            $pid = $values[1];

            switch ($pid) {
                case 1: return null;

                case 2:
                    return [
                        $this->mockModelObject(ContentModel::class, ['id' => 1, 'type' => 'accordionStart']),
                        $this->mockModelObject(ContentModel::class, ['id' => 2, 'type' => 'text']),
                        $this->mockModelObject(ContentModel::class, ['id' => 3, 'type' => 'accordionStop']),
                    ];

                case 3:
                    return [
                        $this->mockModelObject(ContentModel::class, ['id' => 1, 'type' => 'accordionStart']),
                        $this->mockModelObject(ContentModel::class, ['id' => 2, 'type' => 'text']),
                        $this->mockModelObject(ContentModel::class, ['id' => 3, 'type' => 'accordionStop']),
                        $this->mockModelObject(ContentModel::class, ['id' => 4, 'type' => 'headline']),
                        $this->mockModelObject(ContentModel::class, ['id' => 5, 'type' => 'accordionStart']),
                        $this->mockModelObject(ContentModel::class, ['id' => 6, 'type' => 'text']),
                        $this->mockModelObject(ContentModel::class, ['id' => 7, 'type' => 'accordionStop']),
                    ];

                case 4:
                    return [
                        $this->mockModelObject(ContentModel::class, ['id' => 1, 'type' => 'accordionStart']),
                        $this->mockModelObject(ContentModel::class, ['id' => 2, 'type' => 'text']),
                        $this->mockModelObject(ContentModel::class, ['id' => 3, 'type' => 'accordionStop']),
                        $this->mockModelObject(ContentModel::class, ['id' => 4, 'type' => 'headline']),
                        $this->mockModelObject(ContentModel::class, ['id' => 5, 'type' => 'accordionStart']),
                        $this->mockModelObject(ContentModel::class, ['id' => 6, 'type' => 'text']),
                        $this->mockModelObject(ContentModel::class, ['id' => 7, 'type' => 'accordionStop']),
                    ];
            }

            return null;
        });
        $container->set('huh.utils.model', $modelUtilMock);

        $accordionUtil = new AccordionUtil($container);
        $data = [];
        $accordionUtil->structureAccordionStartStop($data);
        $this->assertEmpty($data);

        $data = ['pid' => 1];
        $accordionUtil->structureAccordionStartStop($data);
        $this->assertSame(['pid' => 1], $data);

        $data = ['id' => 1];
        $accordionUtil->structureAccordionStartStop($data);
        $this->assertSame(['id' => 1], $data);

        $data = ['id' => 1, 'pid' => 1];
        $accordionUtil->structureAccordionStartStop($data);
        $this->assertSame(['id' => 1, 'pid' => 1], $data);

        $data = ['id' => 1, 'pid' => 2];
        $accordionUtil->structureAccordionStartStop($data);
        $this->assertArraySubset(['accordion_first' => true, 'accordion_parentId' => 1], $data);
        $this->assertArrayNotHasKey('accordion_last', $data);

        $data = ['id' => 2, 'pid' => 2];
        $accordionUtil->structureAccordionStartStop($data);
        $this->assertSame(['id' => 2, 'pid' => 2], $data);
        $this->assertArrayNotHasKey('accordion_last', $data);

        $data = ['id' => 3, 'pid' => 2];
        $accordionUtil->structureAccordionStartStop($data);
        $this->assertArraySubset(['accordion_last' => true, 'accordion_parentId' => 1], $data);
        $this->assertArrayNotHasKey('accordion_first', $data);

        $data = ['id' => 1, 'pid' => 3];
        $accordionUtil->structureAccordionStartStop($data);
        $this->assertArraySubset(['accordion_first' => true, 'accordion_parentId' => 1], $data);
        $this->assertArrayNotHasKey('accordion_last', $data);

        $data = ['id' => 5, 'pid' => 3];
        $accordionUtil->structureAccordionStartStop($data);
        $this->assertArraySubset(['accordion_first' => true, 'accordion_parentId' => 5], $data);
        $this->assertArrayNotHasKey('accordion_last', $data);

        $data = ['id' => 7, 'pid' => 3];
        $accordionUtil->structureAccordionStartStop($data);
        $this->assertArraySubset(['accordion_last' => true, 'accordion_parentId' => 5], $data);
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
            $container = $this->mockContainer();
        }

        if (!$framework) {
            $framework = $this->mockContaoFramework();
        }
        $container->set('contao.framework', $framework);

        $modelUtil = new ModelUtil($container);
        $container->set('huh.utils.model', $modelUtil);

        return $container;
    }
}

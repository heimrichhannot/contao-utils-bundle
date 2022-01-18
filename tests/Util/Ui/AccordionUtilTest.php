<?php

namespace HeimrichHannot\UtilsBundle\Tests\Util\Ui;

use Contao\ContentModel;
use Contao\Model\Collection;
use HeimrichHannot\TestUtilitiesBundle\Mock\ModelMockTrait;
use HeimrichHannot\UtilsBundle\Tests\AbstractUtilsTestCase;
use HeimrichHannot\UtilsBundle\Util\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Util\Ui\AccordionUtil;
use PHPUnit\Framework\MockObject\MockBuilder;

class AccordionUtilTest extends AbstractUtilsTestCase
{
    use ModelMockTrait;

    public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null)
    {
        $modelUtil = $parameters['modelUtil'] ?? $this->createMock(ModelUtil::class);

        return new AccordionUtil($modelUtil);
    }

    public function structureAccordionStartStopProvider()
    {
        return [
            // Empty (pid=1)
            '1_1' => [[], null],
            '1_2' => [['id' => 1, 'pid' => 1], null],
            '1_3' => [['id' => 1, 'pid' => 1, 'ptable' => 'tl_article'], null],
            // Simple (pid=2)
            '2_1' => [['id' => 1, 'pid' => 2, 'ptable' => 'tl_content'], false, 1, true, false],
            '2_2' => [['id' => 2, 'pid' => 2, 'ptable' => 'tl_content'], false, false, null, null],
            '2_3' => [['id' => 3, 'pid' => 2, 'ptable' => 'tl_content'], false, 1, false, true],
            // Simple (pid=3)
            '3_1' => [['id' => 1, 'pid' => 3, 'ptable' => 'tl_content'], false, 1,     true,  false],
            '3_2' => [['id' => 2, 'pid' => 3, 'ptable' => 'tl_content'], false, false, null,  null],
            '3_3' => [['id' => 3, 'pid' => 3, 'ptable' => 'tl_content'], false, 1,     false, true],
            '3_4' => [['id' => 4, 'pid' => 3, 'ptable' => 'tl_content'], false, false, null,  null],
            '3_5' => [['id' => 5, 'pid' => 3, 'ptable' => 'tl_content'], false, 5,     true,  false],
            '3_6' => [['id' => 6, 'pid' => 3, 'ptable' => 'tl_content'], false, false, null,  null],
            '3_7' => [['id' => 7, 'pid' => 3, 'ptable' => 'tl_content'], false, 5,     false, true],
            // PID = 4
            '4_1' => [['id' => 1, 'pid' => 4, 'ptable' => 'tl_content'], false, 1,     true,  false],
            '4_2' => [['id' => 2, 'pid' => 4, 'ptable' => 'tl_content'], false, false, null,  null],
            '4_3' => [['id' => 3, 'pid' => 4, 'ptable' => 'tl_content'], false, 1,     false, false],
            '4_5' => [['id' => 5, 'pid' => 4, 'ptable' => 'tl_content'], false, 1,     false, false],
            '4_6' => [['id' => 6, 'pid' => 4, 'ptable' => 'tl_content'], false, false, null,  null],
            '4_7' => [['id' => 7, 'pid' => 4, 'ptable' => 'tl_content'], false, 1,     false,  true],
            // PID = 5
            '5_1'  => [['id' => 1,  'pid' => 5, 'ptable' => 'tl_content'], false, 1,     true,  false],
            '5_2'  => [['id' => 2,  'pid' => 5, 'ptable' => 'tl_content'], false, false, null,  null],
            '5_8'  => [['id' => 8,  'pid' => 5, 'ptable' => 'tl_content'], false, 8,     true, false],
            '5_9'  => [['id' => 9,  'pid' => 5, 'ptable' => 'tl_content'], false, false, null, null],
            '5_10' => [['id' => 10, 'pid' => 5, 'ptable' => 'tl_content'], false, 8,     false, true],
            '5_3'  => [['id' => 3,  'pid' => 5, 'ptable' => 'tl_content'], false, 1,     false,  true],
            '5_4'  => [['id' => 4,  'pid' => 5, 'ptable' => 'tl_content'], false, false,  null,  null],
            // PID = 6
            '6_1'  => [['id' => 1,  'pid' => 6, 'ptable' => 'tl_content'], false, 1,     true,  false],
            '6_2'  => [['id' => 2,  'pid' => 6, 'ptable' => 'tl_content'], false, false, null,  null],
            '6_8'  => [['id' => 8,  'pid' => 6, 'ptable' => 'tl_content'], false, 8,     true, false],
            '6_9'  => [['id' => 9,  'pid' => 6, 'ptable' => 'tl_content'], false, false, null, null],
            '6_10' => [['id' => 10, 'pid' => 6, 'ptable' => 'tl_content'], false, 8,     false, true],
            '6_3'  => [['id' => 3,  'pid' => 6, 'ptable' => 'tl_content'], false, 1,     false,  true],
            '6_4'  => [['id' => 4,  'pid' => 6, 'ptable' => 'tl_content'], false, false,  null,  null],
            // PID = 7
            '7_1'  => [['id' => 1,  'pid' => 7, 'ptable' => 'tl_content'], false, 1,     true,  false],
            '7_2'  => [['id' => 2,  'pid' => 7, 'ptable' => 'tl_content'], false, false, null,  null],
            '7_8'  => [['id' => 8,  'pid' => 7, 'ptable' => 'tl_content'], false, 8,     true,  false],
            '7_9'  => [['id' => 9,  'pid' => 7, 'ptable' => 'tl_content'], false, false, null,  null],
            '7_10' => [['id' => 10, 'pid' => 7, 'ptable' => 'tl_content'], false, 8,     false, false],
            '7_12' => [['id' => 12, 'pid' => 7, 'ptable' => 'tl_content'], false, 8,     false, false],
            '7_13' => [['id' => 13, 'pid' => 7, 'ptable' => 'tl_content'], false, false, null,  null],
            '7_14' => [['id' => 14, 'pid' => 7, 'ptable' => 'tl_content'], false, 8,     false, true],
            '7_11' => [['id' => 11, 'pid' => 7, 'ptable' => 'tl_content'], false, false, null,  null],
            '7_3'  => [['id' => 3,  'pid' => 7, 'ptable' => 'tl_content'], false, 1,     false,  true],
            '7_4'  => [['id' => 4,  'pid' => 7, 'ptable' => 'tl_content'], false, false,  null,  null],
        ];
    }

    /**
     * @dataProvider structureAccordionStartStopProvider
     */
    public function testStructureAccordionStartStop($data, $same = false, $parent = false, $first = null, $last = null)
    {
        $modelUtil = $this->createMock(ModelUtil::class);
        $modelUtil->method('findModelInstancesBy')->willReturnCallback(function (string $table, array $columns, array $values, array $options = []) {
            $pid = $values[1];

            switch ($pid) {
                case 1: return null;
                case 2:
                    return new Collection([
                        $this->mockModelObject(ContentModel::class, ['id' => 1, 'type' => 'accordionStart']),
                        $this->mockModelObject(ContentModel::class, ['id' => 2, 'type' => 'text']),
                        $this->mockModelObject(ContentModel::class, ['id' => 3, 'type' => 'accordionStop']),
                    ], 'tl_content');
                case 3:
                    return new Collection([
                        $this->mockModelObject(ContentModel::class, ['id' => 1, 'type' => 'accordionStart']),
                        $this->mockModelObject(ContentModel::class, ['id' => 2, 'type' => 'text']),
                        $this->mockModelObject(ContentModel::class, ['id' => 3, 'type' => 'accordionStop']),
                        $this->mockModelObject(ContentModel::class, ['id' => 4, 'type' => 'headline']),
                        $this->mockModelObject(ContentModel::class, ['id' => 5, 'type' => 'accordionStart']),
                        $this->mockModelObject(ContentModel::class, ['id' => 6, 'type' => 'text']),
                        $this->mockModelObject(ContentModel::class, ['id' => 7, 'type' => 'accordionStop']),
                    ], 'tl_content');
                case 4:
                    return new Collection([
                        $this->mockModelObject(ContentModel::class, ['id' => 1, 'type' => 'accordionStart']),
                        $this->mockModelObject(ContentModel::class, ['id' => 2, 'type' => 'text']),
                        $this->mockModelObject(ContentModel::class, ['id' => 3, 'type' => 'accordionStop']),
                        $this->mockModelObject(ContentModel::class, ['id' => 5, 'type' => 'accordionStart']),
                        $this->mockModelObject(ContentModel::class, ['id' => 6, 'type' => 'text']),
                        $this->mockModelObject(ContentModel::class, ['id' => 7, 'type' => 'accordionStop']),
                    ], 'tl_content');
                case 5:
                    return new Collection([
                        $this->mockModelObject(ContentModel::class, ['id' => 1, 'type' => 'accordionStart']),
                        $this->mockModelObject(ContentModel::class, ['id' => 2, 'type' => 'text']),
                        $this->mockModelObject(ContentModel::class, ['id' => 8, 'type' => 'accordionStart']),
                        $this->mockModelObject(ContentModel::class, ['id' => 9, 'type' => 'text']),
                        $this->mockModelObject(ContentModel::class, ['id' => 10, 'type' => 'accordionStop']),
                        $this->mockModelObject(ContentModel::class, ['id' => 3, 'type' => 'accordionStop']),
                        $this->mockModelObject(ContentModel::class, ['id' => 4, 'type' => 'headline']),
                        $this->mockModelObject(ContentModel::class, ['id' => 5, 'type' => 'accordionStart']),
                        $this->mockModelObject(ContentModel::class, ['id' => 6, 'type' => 'text']),
                        $this->mockModelObject(ContentModel::class, ['id' => 7, 'type' => 'accordionStop']),
                    ], 'tl_content');
                case 6:
                    return new Collection([
                        $this->mockModelObject(ContentModel::class, ['id' => 1, 'type' => 'accordionStart']),
                        $this->mockModelObject(ContentModel::class, ['id' => 2, 'type' => 'text']),
                        $this->mockModelObject(ContentModel::class, ['id' => 8, 'type' => 'accordionStart']),
                        $this->mockModelObject(ContentModel::class, ['id' => 9, 'type' => 'text']),
                        $this->mockModelObject(ContentModel::class, ['id' => 10, 'type' => 'accordionStop']),
                        $this->mockModelObject(ContentModel::class, ['id' => 11, 'type' => 'text']),
                        $this->mockModelObject(ContentModel::class, ['id' => 3, 'type' => 'accordionStop']),
                        $this->mockModelObject(ContentModel::class, ['id' => 4, 'type' => 'headline']),
                        $this->mockModelObject(ContentModel::class, ['id' => 5, 'type' => 'accordionStart']),
                        $this->mockModelObject(ContentModel::class, ['id' => 6, 'type' => 'text']),
                        $this->mockModelObject(ContentModel::class, ['id' => 7, 'type' => 'accordionStop']),
                    ], 'tl_content');
                case 7:
                    return new Collection([
                        $this->mockModelObject(ContentModel::class, ['id' => 1, 'type' => 'accordionStart']),
                        $this->mockModelObject(ContentModel::class, ['id' => 2, 'type' => 'text']),
                        $this->mockModelObject(ContentModel::class, ['id' => 8, 'type' => 'accordionStart']),
                        $this->mockModelObject(ContentModel::class, ['id' => 9, 'type' => 'text']),
                        $this->mockModelObject(ContentModel::class, ['id' => 10, 'type' => 'accordionStop']),
                        $this->mockModelObject(ContentModel::class, ['id' => 12, 'type' => 'accordionStart']),
                        $this->mockModelObject(ContentModel::class, ['id' => 13, 'type' => 'text']),
                        $this->mockModelObject(ContentModel::class, ['id' => 14, 'type' => 'accordionStop']),
                        $this->mockModelObject(ContentModel::class, ['id' => 11, 'type' => 'text']),
                        $this->mockModelObject(ContentModel::class, ['id' => 3, 'type' => 'accordionStop']),
                        $this->mockModelObject(ContentModel::class, ['id' => 4, 'type' => 'headline']),
                        $this->mockModelObject(ContentModel::class, ['id' => 5, 'type' => 'accordionStart']),
                        $this->mockModelObject(ContentModel::class, ['id' => 6, 'type' => 'text']),
                        $this->mockModelObject(ContentModel::class, ['id' => 7, 'type' => 'accordionStop']),
                    ], 'tl_content');
            }
        });

        $instance = $this->getTestInstance(['modelUtil' => $modelUtil]);

        if ($same !== false) {
            if (null === $same) {
                $same = $data;
            }
        }

        $instance->structureAccordionStartStop($data);

        if ($same !== false) {
            $this->assertSame($data, $same);
        }

        if ($parent !== false) {
            $this->assertSame($parent, $data['accordion_parentId']);
        }

        if ($first !== null) {
            $this->assertSame($first, $data['accordion_first']);
        } else {
            $this->assertArrayNotHasKey('accordion_first', $data);
        }
        if ($last !== null) {
            $this->assertSame($last, $data['accordion_last']);
        } else {
            $this->assertArrayNotHasKey('accordion_last', $data);
        }
    }
}
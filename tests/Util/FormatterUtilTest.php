<?php

namespace Util;

use Contao\Controller;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\DataContainer;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Util\FormatterUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;

class FormatterUtilTest extends ContaoTestCase
{
    public function getInstances(array $parameter = []): FormatterUtil
    {
        $parameter['framework'] ??= $this->mockContaoFramework([
            System::class => $this->createMock(System::class),
            Controller::class => $this->createMock(Controller::class)
        ]);
        $parameter['insertTagParser'] ??= $this->createMock(InsertTagParser::class);
        $parameter['utils'] ??= $this->createMock(Utils::class);
        $parameter['system'] ??= $this->createMock(System::class);
        $parameter['kernelBundles'] ??= [
            'multiColumnsEditor' => 'HeimrichHannot\MultiColumnEditorBundle\HeimrichHannotContaoMultiColumnEditorBundle'
        ];

        return new FormatterUtil(
            $parameter['framework'],
            $parameter['insertTagParser'],
            $parameter['utils'],
            $parameter['kernelBundles']
        );
    }

    public function _testFormatDcaFieldValue()
    {
        $formatterUtil = $this->getInstances();

        $dataContainer = $this->createMock(DataContainer::class);
        $dataContainer->table = 'tl_content';

        $this->assertEquals(
            'test',
            $formatterUtil->formatDcaFieldValue(
                $dataContainer,
                'test',
                'test',
                dcaOverride: ['inputType' => 'text']
            )
        );

        $this->assertEquals(
            'foo-bar',
            $formatterUtil->formatDcaFieldValue(
                $dataContainer,
                'test',
                serialize(['value' => 'foo', 'unit' => 'bar']),
                dcaOverride: ['inputType' => 'inputUnit'],
                arrayJoiner: '-'
            )
        );
    }
}
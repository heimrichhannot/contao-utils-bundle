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
    const TAGS_REPLACED = 'tags_replaced';

    public function getInstances(array $parameter = []): FormatterUtil
    {
        $controllerAdapter = $this->mockAdapter(['loadLanguageFile', 'loadDataContainer']);
        $controllerAdapter->method('loadLanguageFile')->willReturn(null);
        $controllerAdapter->method('loadDataContainer')->willReturn(null);

        $insertTagParser = $this->createMock(InsertTagParser::class);
        $insertTagParser->method('replace')->willReturn(static::TAGS_REPLACED);

        $parameter['framework'] ??= $this->mockContaoFramework([
            Controller::class => $controllerAdapter
        ]);
        $parameter['insertTagParser'] ??= $insertTagParser;
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

    public function testFormatDcaFieldValue()
    {
        $formatterUtil = $this->getInstances();

        $dataContainer = $this->createMock(DataContainer::class);
        $dataContainer->table = 'tl_content';

        $this->assertEquals(
            'foo-bar',
            $formatterUtil->formatDcaFieldValue(
                $dataContainer,
                'test',
                serialize(['value' => 'foo', 'unit' => 'bar']),
                FormatterUtil\FormatDcaFieldValueOptions::create()
                    ->setDcaOverride(['inputType' => 'inputUnit'])
                    ->setArrayJoiner('-')
            )
        );

        $this->assertEquals(
            static::TAGS_REPLACED,
            $formatterUtil->formatDcaFieldValue(
                $dataContainer,
                'test',
                'test',
                FormatterUtil\FormatDcaFieldValueOptions::create()
                    ->setDcaOverride(['inputType' => 'text'])
            )
        );
    }
}
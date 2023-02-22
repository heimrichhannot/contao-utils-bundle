<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Util\Dca;

use Contao\Controller;
use HeimrichHannot\UtilsBundle\Tests\AbstractUtilsTestCase;
use HeimrichHannot\UtilsBundle\Util\Dca\DcaUtil;
use PHPUnit\Framework\MockObject\MockBuilder;

class DcaUtilTest extends AbstractUtilsTestCase
{
    public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null)
    {
        $contaoFramework = $parameters['contaoFramework'] ?? $this->mockContaoFramework();

        return new DcaUtil($contaoFramework);
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetPaletteFields(): void
    {
        $controllerAdapter = $this->mockAdapter(['loadDataContainer']);
        $controllerAdapter->method('loadDataContainer')->willReturn(null);

        $contaoFramework = $this->mockContaoFramework([
            Controller::class => $controllerAdapter,
        ]);

        $instance = $this->getTestInstance([
            'contaoFramework' => $contaoFramework,
        ]);

        $GLOBALS['TL_DCA']['tl_content'] = [
            'palettes' => [
                '__selector__' => ['addImage', 'invisible'],
                'default' => '{type_legend},type',
                'text' => '{type_legend},type,headline;{text_legend},text;{image_legend},addImage;{invisible_legend:hide},invisible',
            ],
            'subpalettes' => [
                'addImage' => 'singleSRC',
            ],
        ];

        $this->assertCount(1, $instance->getPaletteFields('tl_content', 'default'));
        $this->assertCount(6, $instance->getPaletteFields('tl_content', 'text'));
    }

    public function testGetDcaFields()
    {
        $controllerAdapter = $this->mockAdapter(['loadDataContainer', 'loadLanguageFile']);
        $controllerAdapter->method('loadDataContainer')->willReturnCallback(function ($table) {
            if ('table' === $table) {
                $GLOBALS['TL_DCA']['table']['fields'] = [
                    'title' => [
                        'label' => ['Title'],
                        'exclude' => true,
                        'search' => true,
                        'inputType' => 'text',
                        'eval' => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true],
                        'sql' => "varchar(255) NOT NULL default ''",
                    ],
                    'addSubmission' => [
                        'label' => ['Submission'],
                        'exclude' => true,
                        'filter' => true,
                        'inputType' => 'checkbox',
                        'eval' => ['doNotCopy' => true, 'submitOnChange' => true],
                    ],
                ];
            }
        });

        $frameworkMock = $this->mockContaoFramework([
            Controller::class => $controllerAdapter,
        ]);

//        $GLOBALS['TL_LANGUAGE'] = 'de';
        $instance = $this->getTestInstance(['contaoFramework' => $frameworkMock]);

        $fields = $instance->getDcaFields('bllaa');
        $this->assertSame([], $fields);

        $fields = $instance->getDcaFields('table');
        $this->assertSame([
            'addSubmission',
            'title',
        ], $fields);

        $fields = $instance->getDcaFields('table', ['allowedInputTypes' => ['select']]);
        $this->assertSame([], $fields);

        $fields = $instance->getDcaFields('table', ['localizeLabels' => true]);
        $this->assertSame([
            'addSubmission' => 'Submission',
            'title' => 'Title',
        ],
            $fields);

        $fields = $instance->getDcaFields('table', ['skipSorting' => true]);
        $this->assertSame([
            'title',
            'addSubmission',
        ], $fields);

        $fields = $instance->getDcaFields('table', ['onlyDatabaseFields' => true]);
        $this->assertSame([
            'title',
        ], $fields);

        $fields = $instance->getDcaFields('table', ['evalConditions' => ['mandatory' => true]]);
        $this->assertSame([
            'title',
        ], $fields);

        $this->expectWarning();
        $instance->getDcaFields('table', ['allowedInputTypes' => 'checkbox']);
    }

    public function testGetDcaFieldsWithWarning()
    {
        $instance = $this->getTestInstance();

        $this->expectWarning();
        $instance->getDcaFields('table', ['evalConditions' => 'mandatory']);
    }
}

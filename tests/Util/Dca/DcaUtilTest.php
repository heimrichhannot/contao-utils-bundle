<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Util\Dca;

use Contao\Controller;
use Exception;
use HeimrichHannot\UtilsBundle\Tests\AbstractUtilsTestCase;
use HeimrichHannot\UtilsBundle\Util\DcaUtil;
use HeimrichHannot\UtilsBundle\Util\DcaUtil\GetDcaFieldsOptions;
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

        $fields = $instance->getDcaFields(
            'table',
            GetDcaFieldsOptions::create()->setAllowedInputTypes(['select'])
        );
        $this->assertSame([], $fields);

        $fields = $instance->getDcaFields(
            'table',
            GetDcaFieldsOptions::create()->setLocalizeLabels(true)
        );
        $this->assertSame([
            'addSubmission' => 'Submission',
            'title' => 'Title',
        ],
            $fields);

        $fields = $instance->getDcaFields(
            'table',
            GetDcaFieldsOptions::create()->setSkipSorting(true)
        );
        $this->assertSame([
            'title',
            'addSubmission',
        ], $fields);

        $fields = $instance->getDcaFields(
            'table',
            GetDcaFieldsOptions::create()->setOnlyDatabaseFields(true)
        );
        $this->assertSame([
            'title',
        ], $fields);

        $fields = $instance->getDcaFields(
            'table',
            GetDcaFieldsOptions::create()->setEvalConditions(['mandatory' => true])
        );
        $this->assertSame([
            'title',
        ], $fields);
    }

    public function testExecuteCallback()
    {
        $instance = $this->getTestInstance();

        $this->assertSame('ham', $instance->executeCallback(function () {
            return 'ham';
        }));

        $this->assertSame('spam', $instance->executeCallback(function ($value) {
            return $value;
        }, 'spam'));

        $this->assertSame('spam_ham', $instance->executeCallback(
            [\HeimrichHannot\UtilsBundle\Util\StringUtil::class, 'camelCaseToSnake'], 'spamHam')
        );

        $this->assertNull($instance->executeCallback(null));
        $this->assertNull($instance->executeCallback([static::class, 'thisIsNotCallable']));
        $this->assertNull($instance->executeCallback(['\This\Is\Unheard\Of', 'notCallable']));
        $this->assertNull($instance->executeCallback(['toFewArguments']));

        try {
            $instance->executeCallback([static::class, 'thisThrowsAnError']);
            $this->fail('An exception should have been thrown');
        } catch (Exception $e) {
            $this->assertSame('I was thrown on purpose', $e->getMessage());
        }
    }

    public function thisThrowsAnError()
    {
        throw new Exception('I was thrown on purpose');
    }
}

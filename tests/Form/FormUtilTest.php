<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\String;

use Contao\Config;
use Contao\Controller;
use Contao\DataContainer;
use Contao\Model;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Driver\DC_Table_Utils;
use HeimrichHannot\UtilsBundle\Form\FormUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FormUtilTest extends ContaoTestCase
{
    /** @var $container ContainerInterface */
    protected $container;

    /** @var $formUtil FormUtil */
    protected $formUtil;

    /** @var $dc DataContainer */
    protected $dc;

    public function setUp()
    {
        $this->container = $this->mockContainer();
        $this->container->set('contao.framework', $this->mockContaoFramework());

        // setup prepareSpecialValueForOutput

        // mock system classes
        $controller = $this->mockAdapter(
            [
                'loadDataContainer',
            ]
        );

        $system = $this->mockAdapter(
            [
                'loadLanguageFile',
            ]
        );

        $this->formUtil = new FormUtil(
            $this->mockContaoFramework(
                [
                    Controller::class => $controller,
                    System::class => $system,
                ]
            )
        );

        if (!\interface_exists('listable')) {
            include_once __DIR__.'/../../vendor/contao/core-bundle/src/Resources/contao/helper/interface.php';
        }

        if (!\function_exists('specialchars')) {
            include_once __DIR__.'/../../vendor/contao/core-bundle/src/Resources/contao/helper/functions.php';
        }

        $dcaUtil = $this->mockAdapter(['getConfigByArrayOrCallbackOrFunction']);
        $dcaUtil->method('getConfigByArrayOrCallbackOrFunction')->willReturn(false);
        $this->container->set('huh.utils.dca', $dcaUtil);

        $foreignKeyInstance1 = $this->createMock(Model::class);
        $foreignKeyInstance1->method('__get')->willReturnCallback(function ($key) {
            switch ($key) {
                case 'title':
                    return 'Foreign key title 1';
            }

            return '';
        });

        $foreignKeyInstance3 = $this->createMock(Model::class);
        $foreignKeyInstance3->method('__get')->willReturnCallback(function ($key) {
            switch ($key) {
                case 'title':
                    return 'Foreign key title 3';
            }

            return '';
        });

        $modelUtil = $this->mockAdapter(['findModelInstanceByPk']);
        $modelUtil->method('findModelInstanceByPk')->willReturnCallback(function ($table, $id) use ($foreignKeyInstance1, $foreignKeyInstance3) {
            switch ($id) {
                case 'first':
                    return $foreignKeyInstance1;
                case 'third':
                    return $foreignKeyInstance3;
            }

            return null;
        });
        $this->container->set('huh.utils.model', $modelUtil);

        // mock data container
        $this->dc = $this->getMockBuilder(DC_Table_Utils::class)->disableOriginalConstructor()->getMock();
        $this->dc->method('__get')->willReturnCallback(function ($key) {
            switch ($key) {
                case 'table':
                    return 'tl_test';
                case 'id':
                    return 1;
                case 'field':
                    return 'myField';
            }

            return '';
        });

        // mock language
        $GLOBALS['TL_LANG']['tl_test']['myField'] = ['My field', 'This field is the test field.'];
        $GLOBALS['TL_LANG']['tl_test']['myFieldExplanation'] = '<h1>Mein Feld</h1>';
        $GLOBALS['TL_LANG']['tl_test']['reference'] = [
            'first' => 'Erster',
        ];

        System::setContainer($this->container);
    }

    public function testPrepareSpecialValueForOutputText()
    {
        $result = $this->formUtil->prepareSpecialValueForOutput('myField', 'myValue', $this->dc);

        // at first call without dca being set
        $this->assertSame('myValue', $result);

        // mock dca
        $GLOBALS['TL_DCA']['tl_test']['fields']['myField'] = [
            'label' => &$GLOBALS['TL_LANG']['tl_test']['myField'],
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ];

        $result = $this->formUtil->prepareSpecialValueForOutput('myField', 'myValue', $this->dc);

        $this->assertSame('myValue', $result);

        // test date & time
        $time = time();

        // test with rgxp=date
        Config::set('dateFormat', 'd.m.Y');
        $GLOBALS['TL_DCA']['tl_test']['fields']['myField']['eval']['rgxp'] = 'date';

        $result = $this->formUtil->prepareSpecialValueForOutput('myField', $time, $this->dc);

        $this->assertSame(date('d.m.Y', $time), $result);

        // test with rgxp=datim
        Config::set('datimFormat', 'd.m.Y H:i');
        $GLOBALS['TL_DCA']['tl_test']['fields']['myField']['eval']['rgxp'] = 'datim';

        $result = $this->formUtil->prepareSpecialValueForOutput('myField', $time, $this->dc);

        $this->assertSame(date('d.m.Y H:i', $time), $result);

        // test with rgxp=time
        Config::set('timeFormat', 'H:i');
        $GLOBALS['TL_DCA']['tl_test']['fields']['myField']['eval']['rgxp'] = 'time';

        $result = $this->formUtil->prepareSpecialValueForOutput('myField', $time, $this->dc);

        $this->assertSame(date('H:i', $time), $result);

        // test encryption
        unset($GLOBALS['TL_DCA']['tl_test']['fields']['myField']['eval']['rgxp']);
        $GLOBALS['TL_DCA']['tl_test']['fields']['myField']['eval']['encrypt'] = true;

        $result = $this->formUtil->prepareSpecialValueForOutput('myField', @\Encryption::encrypt('myValue'), $this->dc);

        $this->assertSame('myValue', $result);
    }

    public function testPrepareSpecialValueForOutputArray()
    {
        // mock dca
        $GLOBALS['TL_DCA']['tl_test']['fields']['myField'] = [
            'label' => &$GLOBALS['TL_LANG']['tl_test']['myField'],
            'inputType' => 'select',
            'reference' => &$GLOBALS['TL_LANG']['tl_test']['reference'],
            'eval' => ['tl_class' => 'w50', 'mandatory' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ];

        // test with removing empty values
        $result = $this->formUtil->prepareSpecialValueForOutput('myField', [
            'first', '', 'third',
        ], $this->dc);

        $this->assertSame('Erster, third', $result);

        // test without option caching
        $result = $this->formUtil->prepareSpecialValueForOutput('myField', [
            'first', '', 'third',
        ], $this->dc, [
            'skipOptionCaching' => true,
        ]);

        $this->assertSame('Erster, third', $result);

        // test with skipping localization
        $result = $this->formUtil->prepareSpecialValueForOutput('myField', [
            'first', '', 'third',
        ], $this->dc, [
            'skipLocalization' => true,
        ]);

        $this->assertSame('first, third', $result);

        // test with removing empty values
        $result = $this->formUtil->prepareSpecialValueForOutput('myField', [
            'first', '', 'third',
        ], $this->dc, [
            'preserveEmptyArrayValues' => true,
        ]);

        $this->assertSame('Erster, , third', $result);

        // test foreignKey
        $GLOBALS['TL_DCA']['tl_test']['fields']['myField']['foreignKey'] = 'tl_test2.title';

        $result = $this->formUtil->prepareSpecialValueForOutput('myField', [
            'first', 'third',
        ], $this->dc);

        $this->assertSame('Foreign key title 1, Foreign key title 3', $result);
    }

    public function testPrepareSpecialValueForOutputExplanation()
    {
        // mock dca
        $GLOBALS['TL_DCA']['tl_test']['fields']['myField'] = [
            'label' => &$GLOBALS['TL_LANG']['tl_test']['myField'],
            'inputType' => 'explanation',
            'eval' => ['text' => $GLOBALS['TL_LANG']['tl_test']['myFieldExplanation']],
        ];

        $result = $this->formUtil->prepareSpecialValueForOutput('myField', '', $this->dc);

        $this->assertSame('<h1>Mein Feld</h1>', $result);
    }
}

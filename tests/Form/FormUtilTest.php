<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\String;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\DataContainer;
use Contao\Environment;
use Contao\Model;
use Contao\Model\Collection;
use Contao\StringUtil;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\UtilsBundle\Driver\DC_Table_Utils;
use HeimrichHannot\UtilsBundle\Form\FormUtil;
use HeimrichHannot\UtilsBundle\Model\CfgTagModel;
use HeimrichHannot\UtilsBundle\Security\EncryptionUtil;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpFoundation\RequestStack;

class FormUtilTest extends ContaoTestCase
{
    /** @var $dc DataContainer */
    protected $dc;

    public function setUp()
    {
        if (!interface_exists('listable')) {
            include_once __DIR__.'/../../vendor/contao/core-bundle/src/Resources/contao/helper/interface.php';
        }

        if (!\function_exists('specialchars')) {
            include_once __DIR__.'/../../vendor/contao/core-bundle/src/Resources/contao/helper/functions.php';
        }

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
        $GLOBALS['TL_LANG']['tl_test']['firstname'] = ['Firstname', ''];
        $GLOBALS['TL_LANG']['tl_test']['lastname'] = ['Lastname', ''];
        $GLOBALS['TL_LANG']['tl_test']['language'] = ['Language', ''];
        $GLOBALS['TL_LANG']['tl_test']['myFieldExplanation'] = '<h1>Mein Feld</h1>';
        $GLOBALS['TL_LANG']['tl_test']['reference'] = [
            'first' => 'Erster',
            'de' => 'Deutsch',
            'en' => 'Englisch',
        ];

        $GLOBALS['TL_LANG']['MSC']['yes'] = 'Ja';
        $GLOBALS['TL_LANG']['MSC']['no'] = 'Nein';
    }

    public function testPrepareSpecialValueForOutputText()
    {
        $formUtil = $this->getFormUtilMock();
        $result = $formUtil->prepareSpecialValueForOutput('myField', 'myValue', $this->dc);

        // at first call without dca being set
        $this->assertSame('myValue', $result);

        // mock dca
        $GLOBALS['TL_DCA']['tl_test']['fields']['myField'] = [
            'label' => &$GLOBALS['TL_LANG']['tl_test']['myField'],
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ];

        $result = $formUtil->prepareSpecialValueForOutput('myField', 'myValue', $this->dc);

        $this->assertSame('myValue', $result);

        // test date & time
        $time = time();

        // test with rgxp=date
        Config::set('dateFormat', 'd.m.Y');
        $GLOBALS['TL_DCA']['tl_test']['fields']['myField']['eval']['rgxp'] = 'date';

        $result = $formUtil->prepareSpecialValueForOutput('myField', $time, $this->dc);

        $this->assertSame(date('d.m.Y', $time), $result);

        // test with rgxp=datim
        Config::set('datimFormat', 'd.m.Y H:i');
        $GLOBALS['TL_DCA']['tl_test']['fields']['myField']['eval']['rgxp'] = 'datim';

        $result = $formUtil->prepareSpecialValueForOutput('myField', $time, $this->dc);

        $this->assertSame(date('d.m.Y H:i', $time), $result);

        // test with rgxp=time
        Config::set('timeFormat', 'H:i');
        $GLOBALS['TL_DCA']['tl_test']['fields']['myField']['eval']['rgxp'] = 'time';

        $result = $formUtil->prepareSpecialValueForOutput('myField', $time, $this->dc);

        $this->assertSame(date('H:i', $time), $result);

        // test encryption
        unset($GLOBALS['TL_DCA']['tl_test']['fields']['myField']['eval']['rgxp']);
        $GLOBALS['TL_DCA']['tl_test']['fields']['myField']['eval']['encrypt'] = true;

        $plain = 'This is a test :-)(/$§()$/$=)___  fds';
        list($encrypted, $iv) = $this->getContainerMock()->get('huh.utils.encryption')->encrypt($plain);

        $result = $formUtil->prepareSpecialValueForOutput('myField', $encrypted.'.'.$iv, $this->dc);

        $this->assertSame($plain, $result);
    }

    public function testPrepareSpecialValueForOutputArray()
    {
        $formUtil = $this->getFormUtilMock();
        // mock dca
        $GLOBALS['TL_DCA']['tl_test']['fields']['myField'] = [
            'label' => &$GLOBALS['TL_LANG']['tl_test']['myField'],
            'inputType' => 'select',
            'reference' => &$GLOBALS['TL_LANG']['tl_test']['reference'],
            'eval' => ['tl_class' => 'w50', 'mandatory' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ];

        // test with removing empty values
        $result = $formUtil->prepareSpecialValueForOutput('myField', [
            'first',
            '',
            'third',
        ], $this->dc);

        $this->assertSame('Erster, third', $result);

        // test with skipping localization
        $result = $formUtil->prepareSpecialValueForOutput('myField', [
            'first',
            '',
            'third',
        ], $this->dc, [
            'skipLocalization' => true,
        ]);

        $this->assertSame('first, third', $result);

        // test with removing empty values
        $result = $formUtil->prepareSpecialValueForOutput('myField', [
            'first',
            '',
            'third',
        ], $this->dc, [
            'preserveEmptyArrayValues' => true,
        ]);

        $this->assertSame('Erster, , third', $result);

        // test foreignKey
        $GLOBALS['TL_DCA']['tl_test']['fields']['myField']['foreignKey'] = 'tl_test2.title';

        $result = $formUtil->prepareSpecialValueForOutput('myField', [
            'first',
            'third',
        ], $this->dc);

        $this->assertSame('Foreign key title 1, Foreign key title 3', $result);
    }

    public function testPrepareSpecialValueForOutputExplanation()
    {
        $formUtil = $this->getFormUtilMock();
        // mock dca
        $GLOBALS['TL_DCA']['tl_test']['fields']['myField'] = [
            'label' => &$GLOBALS['TL_LANG']['tl_test']['myField'],
            'inputType' => 'explanation',
            'eval' => ['text' => $GLOBALS['TL_LANG']['tl_test']['myFieldExplanation']],
        ];

        $result = $formUtil->prepareSpecialValueForOutput('myField', '', $this->dc);

        $this->assertSame('<h1>Mein Feld</h1>', $result);
    }

    public function testPrepareSpecialValueForOutputCfgTags()
    {
        $formUtil = $this->getFormUtilMock();
        // mock dca
        $GLOBALS['TL_DCA']['tl_test']['fields']['myField'] = [
            'label' => &$GLOBALS['TL_LANG']['tl_test']['myField'],
            'inputType' => 'cfgTags',
            'eval' => [
                'tagsManager' => 'app.test',
            ],
            'relation' => [
                'relationTable' => 'tl_test_tags',
            ],
            'foreignKey' => 'tl_cfg_tag.name', // required for back end filter value to name conversion
        ];

        $result = $formUtil->prepareSpecialValueForOutput('myField', '', $this->dc);

        $this->assertSame('First tag, Third tag', $result);
    }

    public function testPrepareSpecialValueForOutputUuid()
    {
        $formUtil = $this->getFormUtilMock();
        $value = StringUtil::uuidToBin('82f9119db59b11e787f2a08cfddc0261');
        Environment::set('url', 'http://localhost');

        // mock dca
        $GLOBALS['TL_DCA']['tl_test']['fields']['myField'] = [
            'label' => &$GLOBALS['TL_LANG']['tl_test']['myField'],
            'inputType' => 'fileTree',
        ];

        $result = $formUtil->prepareSpecialValueForOutput('myField', $value, $this->dc);

        $this->assertSame('http://localhost/files/themes/img/myimage.png', $result);
    }

    public function testPrepareSpecialValueForOutputIsBoolean()
    {
        $GLOBALS['TL_DCA']['tl_test']['fields']['myField'] = [
            'label' => &$GLOBALS['TL_LANG']['tl_test']['myField'],
            'inputType' => 'checkbox',
            'eval' => [
                'multiple' => true,
            ],
        ];

        // dca util
        $dcaUtil = $this->mockAdapter(['getConfigByArrayOrCallbackOrFunction']);
        $dcaUtil->method('getConfigByArrayOrCallbackOrFunction')->willReturn([
            'first' => 'Erster',
            'third' => 'Dritter',
        ], [
            'skipOptionCaching' => true,
        ]);

        $container = $this->mockContainer();
        $container->set('huh.utils.dca', $dcaUtil);
        $formUtil = $this->getFormUtilMock($container);

        $result = $formUtil->prepareSpecialValueForOutput('myField', ['first', 'third'], $this->dc);
        $this->assertSame('Erster, Dritter', $result);

        // mock dca
        $GLOBALS['TL_DCA']['tl_test']['fields']['myField'] = [
            'label' => &$GLOBALS['TL_LANG']['tl_test']['myField'],
            'inputType' => 'checkbox',
            'eval' => [
                'isBoolean' => true,
            ],
        ];

        $result = $formUtil->prepareSpecialValueForOutput('myField', '1', $this->dc);
        $this->assertSame('Ja', $result);

        $result = $formUtil->prepareSpecialValueForOutput('myField', '', $this->dc);
        $this->assertSame('Nein', $result);
    }

    public function testPrepareSpecialValueForOutputMultiColumnEditor()
    {
        $formUtil = $this->getFormUtilMock();

        $value = [
            ['firstname' => 'John1', 'lastname' => 'Doe1', 'language' => 'de'],
            ['firstname' => 'John2', 'lastname' => 'Doe2', 'language' => 'en'],
        ];

        $GLOBALS['TL_DCA']['tl_test']['fields']['myField'] = [
            'label' => &$GLOBALS['TL_LANG']['tl_test']['myField'],
            'inputType' => 'multiColumnEditor',
            'eval' => [
                'multiColumnEditor' => [
                    'fields' => [
                        'firstname' => [
                            'label' => &$GLOBALS['TL_LANG']['tl_test']['firstname'],
                            'inputType' => 'text',
                        ],
                        'lastname' => [
                            'label' => &$GLOBALS['TL_LANG']['tl_test']['lastname'],
                            'inputType' => 'text',
                        ],
                        'language' => [
                            'label' => &$GLOBALS['TL_LANG']['tl_test']['language'],
                            'inputType' => 'select',
                            'options' => ['de', 'en'],
                            'reference' => &$GLOBALS['TL_LANG']['tl_test']['reference'],
                        ],
                    ],
                ],
            ],
            'sql' => 'blob NULL',
        ];

        $result = $formUtil->prepareSpecialValueForOutput('myField', $value, $this->dc);
        $this->assertSame("\t\n\tFirstname: John1\tLastname: Doe1\tLanguage: Deutsch\t\n\tFirstname: John2\tLastname: Doe2\tLanguage: Englisch\t\n", $result);

        $adapter = $this->mockAdapter(['getConfigByArrayOrCallbackOrFunction']);
        $adapter->method('getConfigByArrayOrCallbackOrFunction')->willThrowException(new \ErrorException('This is an error exception'));

        $container = $this->mockContainer();
        $container->set('huh.utils.dca', $adapter);
        $formUtil = $this->getFormUtilMock($container);

        $result = $formUtil->prepareSpecialValueForOutput('myField', $value, $this->dc);
        $this->assertSame("\t\n\tFirstname: John1\tLastname: Doe1\tLanguage: Deutsch\t\n\tFirstname: John2\tLastname: Doe2\tLanguage: Englisch\t\n", $result);
    }

    public function testEscapeAllHtmlEntities()
    {
        $formUtil = $this->getFormUtilMock();

        $GLOBALS['TL_DCA']['tl_test']['fields']['myField'] = [
            'label' => &$GLOBALS['TL_LANG']['tl_test']['myField'],
            'inputType' => 'multiColumnEditor',
            'eval' => ['allowHtml' => true],
            'options' => ['test', 'test'],
            'sql' => 'blob NULL',
        ];

        $result = $formUtil->escapeAllHtmlEntities('tl_test', 'myField', '<script>alert()</script>');
        $this->assertSame('&#60;script&#62;alert()&#60;/script&#62;', $result);

        $GLOBALS['TL_DCA']['tl_test']['fields']['myField'] = [
            'label' => &$GLOBALS['TL_LANG']['tl_test']['myField'],
            'inputType' => 'multiColumnEditor',
            'eval' => ['allowHtml' => false, 'rte' => '', 'preserveTags' => ''],
            'options' => ['test', 'test'],
            'sql' => 'blob NULL',
        ];

        $result = $formUtil->escapeAllHtmlEntities('tl_test', 'myField', '<script>alert()</script>');
        $this->assertSame('&#60;script&#62;alert()&#60;/script&#62;', $result);

        $GLOBALS['TL_DCA']['tl_test']['fields']['myField'] = [
            'label' => &$GLOBALS['TL_LANG']['tl_test']['myField'],
            'inputType' => 'multiColumnEditor',
            'eval' => ['allowHtml' => false, 'rte' => '', 'preserveTags' => '', 'decodeEntities' => true],
            'options' => 'test',
            'sql' => 'blob NULL',
        ];

        $result = $formUtil->escapeAllHtmlEntities('tl_test', 'myField', '<script>alert()</script>');
        $this->assertSame('&#60;script&#62;alert&#40;&#41;&#60;/script&#62;', $result);

        $result = $formUtil->escapeAllHtmlEntities('tl_test', 'myField', false);
        $this->assertFalse($result);
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected function getContainerMock(ContainerBuilder $container = null)
    {
        if (!$container) {
            $container = $this->mockContainer();
        }
        $container->set('contao.framework', $this->mockContaoFramework());

        $requestStack = new RequestStack();
        $requestStack->push(new \Symfony\Component\HttpFoundation\Request());
        $backendMatcher = new RequestMatcher('/contao', 'test.com', null, ['192.168.1.0']);
        $frontendMatcher = new RequestMatcher('/index', 'test.com', null, ['192.168.1.0']);
        $scopeMatcher = new ScopeMatcher($backendMatcher, $frontendMatcher);
        $request = new Request($this->mockContaoFramework(), $requestStack, $scopeMatcher);
        $container->set('huh.request', $request);

        if (!$container->has('huh.utils.dca')) {
            $dcaUtil = $this->mockAdapter(['getConfigByArrayOrCallbackOrFunction']);
            $dcaUtil->method('getConfigByArrayOrCallbackOrFunction')->willReturn(null);
            $container->set('huh.utils.dca', $dcaUtil);
        }

        $fileUtil = $this->mockAdapter(['getPathFromUuid']);
        $fileUtil->method('getPathFromUuid')->willReturn('files/themes/img/myimage.png');
        $container->set('huh.utils.file', $fileUtil);

        $containerUtil = $this->mockAdapter(['isBundleActive']);
        $containerUtil->method('isBundleActive')->willReturn(true);
        $container->set('huh.utils.container', $containerUtil);

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
        $container->set('huh.utils.model', $modelUtil);

        $encryptionUtils = new EncryptionUtil($this->mockContaoFramework());
        $container->set('huh.utils.encryption', $encryptionUtils);

        $container->setParameter('secret', Config::class);

        System::setContainer($container);

        return $container;
    }

    protected function getFormUtilMock(ContainerBuilder $container = null)
    {
        // mock system classes
        $controller = $this->mockAdapter([
            'loadDataContainer',
        ]);

        $system = $this->mockAdapter([
            'loadLanguageFile',
        ]);

        $cfgTagModel = $this->mockAdapter([
            'findBy',
        ]);

        $tagModelCollection = $this->createMock(Collection::class);
        $tagModelCollection->method('fetchEach')->willReturn(['First tag', 'Third tag']);

        $cfgTagModel->method('findBy')->willReturn($tagModelCollection);

        $formUtil = new FormUtil(
            $this->getContainerMock($container),
            $this->mockContaoFramework(
            [
                Controller::class => $controller,
                System::class => $system,
                CfgTagModel::class => $cfgTagModel,
            ]));

        return $formUtil;
    }
}

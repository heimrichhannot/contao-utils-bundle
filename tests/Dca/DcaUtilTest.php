<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Dca;

use Contao\BackendUser;
use Contao\Database;
use Contao\DataContainer;
use Contao\FrontendUser;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\UtilsBundle\Arrays\ArrayUtil;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Tests\TestCaseEnvironment;
use Symfony\Component\Translation\Translator;

class DcaUtilTest extends TestCaseEnvironment
{
    public function setUp()
    {
        parent::setUp();

        if (!defined('FE_USER_LOGGED_IN')) {
            define('FE_USER_LOGGED_IN', true);
        }

        $container = System::getContainer();

        $translator = new Translator('de');
        $container->set('translator', $translator);

        $mockedModel = $this->mockClassWithProperties(Model::class, ['overrideTitle' => 'title', 'title' => 'title', 'author' => null, 'authorType' => 'none']);
        $mockedModel->method('save');
        $utilsModel = $this->createMock(ModelUtil::class);
        $utilsModel->method('findModelInstanceByPk')->willReturn($mockedModel);
        $container->set('huh.utils.model', $utilsModel);

        $choiceModel = $this->mockAdapter(['getCachedChoices']);
        $choiceModel->method('getCachedChoices')->willReturn(['dataContainer' => 'data', 'labelPattern' => 'label']);
        $container->set('huh.utils.choice.model_instance', $choiceModel);

        $containerUtils = $this->mockAdapter(['isFrontend', 'isBackend']);
        $containerUtils->method('isFrontend')->willReturn(true);
        $containerUtils->method('isBackend')->willReturn(true);
        $container->set('huh.utils.container', $containerUtils);

        $arrayUtil = new ArrayUtil($this->mockContaoFramework());
        $container->set('huh.utils.array', $arrayUtil);

        System::setContainer($container);
    }

    public function testInstantiation()
    {
        $util = new DcaUtil($this->mockContaoFramework());
        $this->assertInstanceOf(DcaUtil::class, $util);
    }

    public function testGetConfigByArrayOrCallbackOrFunction()
    {
        $dcaUtil = new DcaUtil($this->mockContaoFramework());

        $result = $dcaUtil->getConfigByArrayOrCallbackOrFunction(['array' => true], 'array');
        $this->assertTrue($result);

        $result = $dcaUtil->getConfigByArrayOrCallbackOrFunction(['array' => true], 'arrays');
        $this->assertNull($result);

        $result = $dcaUtil->getConfigByArrayOrCallbackOrFunction(['array_callback' => true], 'array');
        $this->assertNull($result);

        $result = $dcaUtil->getConfigByArrayOrCallbackOrFunction(['test_callback' => function ($arguments) { return $arguments; }], 'test', ['test']);
        $this->assertSame('test', $result);

        $result = $dcaUtil->getConfigByArrayOrCallbackOrFunction(['deserialize_callback' => [StringUtil::class, 'deserialize']], 'deserialize', ['test']);
        $this->assertSame('test', $result);

        $result = $dcaUtil->getConfigByArrayOrCallbackOrFunction(['array_callback' => ['test', 'test']], 'array');
        $this->assertNull($result);

        $result = $dcaUtil->getConfigByArrayOrCallbackOrFunction(['deserialize_callback' => [StringUtil::class, 'deseridalize']], 'deserialize', ['test']);
        $this->assertNull($result);
    }

    public function testSetDateAdded()
    {
        $model = $this->mockClassWithProperties(Model::class, ['dateAdded' => 0]);
        $modelUtils = $this->mockAdapter(['findModelInstanceByPk']);
        $modelUtils->method('findModelInstanceByPk')->willReturn($model);

        $container = System::getContainer();
        $container->set('huh.utils.model', $modelUtils);
        System::setContainer($container);

        $databaseAdapter = $this->mockAdapter(['prepare', 'execute']);
        $databaseAdapter->method('prepare')->willReturnSelf();
        $databaseAdapter->method('execute');

        $dcaUtil = new DcaUtil($this->mockContaoFramework([Database::class => $databaseAdapter]));
        $dcaUtil->setDateAdded($this->getDataContainerMock());

        // fail run
        $model = $this->mockClassWithProperties(Model::class, ['dateAdded' => 10]);
        $modelUtils = $this->mockAdapter(['findModelInstanceByPk']);
        $modelUtils->method('findModelInstanceByPk')->willReturn($model);

        $container = System::getContainer();
        $container->set('huh.utils.model', $modelUtils);
        System::setContainer($container);

        $databaseAdapter = $this->mockAdapter(['prepare', 'execute']);
        $databaseAdapter->method('prepare')->willReturnSelf();
        $databaseAdapter->method('execute');

        $dcaUtil = new DcaUtil($this->mockContaoFramework([Database::class => $databaseAdapter]));
        $result = $dcaUtil->setDateAdded($this->getDataContainerMock());
        $this->assertNull($result);
    }

    public function testSetDateAddedOnCopy()
    {
        $model = $this->mockClassWithProperties(Model::class, ['dateAdded' => 0]);
        $modelUtils = $this->mockAdapter(['findModelInstanceByPk']);
        $modelUtils->method('findModelInstanceByPk')->willReturn($model);

        $container = System::getContainer();
        $container->set('huh.utils.model', $modelUtils);
        System::setContainer($container);

        $databaseAdapter = $this->mockAdapter(['prepare', 'execute']);
        $databaseAdapter->method('prepare')->willReturnSelf();
        $databaseAdapter->method('execute');

        $dcaUtil = new DcaUtil($this->mockContaoFramework([Database::class => $databaseAdapter]));
        $dcaUtil->setDateAddedOnCopy(1, $this->getDataContainerMock());

        // fail run
        $model = $this->mockClassWithProperties(Model::class, ['dateAdded' => 10]);
        $modelUtils = $this->mockAdapter(['findModelInstanceByPk']);
        $modelUtils->method('findModelInstanceByPk')->willReturn($model);

        $container = System::getContainer();
        $container->set('huh.utils.model', $modelUtils);
        System::setContainer($container);

        $databaseAdapter = $this->mockAdapter(['prepare', 'execute']);
        $databaseAdapter->method('prepare')->willReturnSelf();
        $databaseAdapter->method('execute');

        $dcaUtil = new DcaUtil($this->mockContaoFramework([Database::class => $databaseAdapter]));
        $result = $dcaUtil->setDateAddedOnCopy(1, $this->getDataContainerMock());
        $this->assertNull($result);
    }

    public function testGetFields()
    {
        $GLOBALS['TL_DCA']['table']['fields'] = [
            'title' => [
                'label' => ['this is a title'],
                'exclude' => true,
                'search' => true,
                'inputType' => 'text',
                'eval' => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true],
                'sql' => "varchar(255) NOT NULL default ''",
            ],
            'addSubmission' => [
                'label' => ['this is a title'],
                'exclude' => true,
                'filter' => true,
                'inputType' => 'checkbox',
                'eval' => ['doNotCopy' => true, 'submitOnChange' => true],
                'sql' => "char(1) NOT NULL default ''",
            ],
        ];
        $GLOBALS['TL_LANGUAGE'] = 'de';
        $dcaUtil = new DcaUtil($this->mockContaoFramework());

        $fields = $dcaUtil->getFields(false);
        $this->assertSame([], $fields);

        $fields = $dcaUtil->getFields('table');
        $this->assertSame(['title' => 'this is a title [title]', 'addSubmission' => 'this is a title [addSubmission]'], $fields);

        $fields = $dcaUtil->getFields('table', ['inputTypes' => ['select']]);
        $this->assertSame([], $fields);

        $fields = $dcaUtil->getFields('table', ['localizeLabels' => false]);
        $this->assertSame(['title' => 'title', 'addSubmission' => 'addSubmission'], $fields);

        $fields = $dcaUtil->getFields('table', ['skipSorting' => false]);
        $this->assertSame(['addSubmission' => 'this is a title [addSubmission]', 'title' => 'this is a title [title]'], $fields);
    }

    public function testAddOverridableFields()
    {
        $GLOBALS['TL_LANG']['destinationTable']['overrideTitle'] = ['overrideTitle'];
        $GLOBALS['TL_LANG']['destinationTable']['overrideAddSubmission'] = ['overrideAddSubmission'];

        $GLOBALS['TL_DCA']['sourceTable'] = [
            'fields' => [
                'title' => [
                    'label' => ['this is a title'],
                    'exclude' => true,
                    'search' => true,
                    'inputType' => 'text',
                    'eval' => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true],
                    'sql' => "varchar(255) NOT NULL default ''",
                ],
                'addSubmission' => [
                    'label' => ['this is a title'],
                    'exclude' => true,
                    'filter' => true,
                    'inputType' => 'checkbox',
                    'eval' => ['doNotCopy' => true, 'submitOnChange' => true],
                    'sql' => "char(1) NOT NULL default ''",
                ],
            ],
        ];

        $GLOBALS['TL_DCA']['destinationTable'] = ['palettes' => ['__selector__' => [], 'default' => '{general_legend},title, text;{submission_legend},addSubmission;{publish_legend},published'], 'subpalettes' => ['overrideTitle_test' => 'title', 'addSubmission']];

        $dcaUtil = new DcaUtil($this->mockContaoFramework());
        $dcaUtil->addOverridableFields(['title', 'addSubmission'], 'sourceTable', 'destinationTable');

        $this->assertSame($GLOBALS['TL_DCA']['sourceTable']['fields']['title'], $GLOBALS['TL_DCA']['destinationTable']['fields']['title']);
        $this->assertSame($GLOBALS['TL_DCA']['sourceTable']['fields']['addSubmission'], $GLOBALS['TL_DCA']['destinationTable']['fields']['addSubmission']);
        $this->assertTrue(isset($GLOBALS['TL_DCA']['destinationTable']['fields']['overrideTitle']));
        $this->assertSame([
            'label' => ['overrideTitle'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50', 'submitOnChange' => true, 'isOverrideSelector' => true],
            'sql' => "char(1) NOT NULL default ''",
        ], $GLOBALS['TL_DCA']['destinationTable']['fields']['overrideTitle']);
        $this->assertTrue(isset($GLOBALS['TL_DCA']['destinationTable']['fields']['overrideAddSubmission']));
        $this->assertSame([
            'label' => ['overrideAddSubmission'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50', 'submitOnChange' => true, 'isOverrideSelector' => true],
            'sql' => "char(1) NOT NULL default ''",
        ], $GLOBALS['TL_DCA']['destinationTable']['fields']['overrideAddSubmission']);

        $dcaUtil->addOverridableFields(['title', 'addSubmission'], 'sourceTable', 'destinationTable', ['checkboxDcaEvalOverride' => ['tl_class' => 'test'], 'skipLocalization' => false]);
        $this->assertSame('test', $GLOBALS['TL_DCA']['destinationTable']['fields']['overrideAddSubmission']['eval']['tl_class']);
        $this->assertSame('test', $GLOBALS['TL_DCA']['destinationTable']['fields']['overrideTitle']['eval']['tl_class']);
        $this->assertSame(['huh.utils.misc.override.label', 'huh.utils.misc.override.desc'], $GLOBALS['TL_LANG']['destinationTable']['overrideAddSubmission']);
        $this->assertSame(['huh.utils.misc.override.label', 'huh.utils.misc.override.desc'], $GLOBALS['TL_LANG']['destinationTable']['overrideTitle']);
    }

    public function testGetOverridableProperty()
    {
        $utilsModelMocked = $this->mockClassWithProperties(Model::class, ['overrideTitle2' => 'title2', 'title2' => 'title2']);

        $dcaUtil = new DcaUtil($this->mockContaoFramework());
        $result = $dcaUtil->getOverridableProperty('title', [$utilsModelMocked, 'instance' => ['table', 'pk']]);

        $this->assertSame('title', $result);
    }

    public function testFlattenPaletteForSubEntities()
    {
        $dcaUtil = new DcaUtil($this->mockContaoFramework());
        $dcaUtil->flattenPaletteForSubEntities('destinationTable', ['overrideTitle', 'overrideAddSubmission']);
        $this->assertSame(['addSubmission'], $GLOBALS['TL_DCA']['destinationTable']['subpalettes']);
    }

    public function testGenerateAlias()
    {
        $util = new DcaUtil($this->mockContaoFramework([
            Database::class => $this->getDatabaseMock(),
        ]));

        $this->assertSame('alias', $util->generateAlias('alias', 15, 'tl_table', 'Alias'));
        $this->assertSame('alias', $util->generateAlias('', 15, 'tl_table', 'Alias'));
        $this->assertSame('hans-dieter', $util->generateAlias('', 15, 'tl_table', 'Hans Dieter'));
        $this->assertSame('hans-däter', $util->generateAlias('', 15, 'tl_table', 'Hans Däter'));
        $this->assertSame('hans-daeter', $util->generateAlias('', 15, 'tl_table', 'Hans Däter', false));
        $this->assertSame('existing-alias', $util->generateAlias('', 1, 'tl_table', 'Existing Alias'));
        $this->assertSame('existing-alias-5', $util->generateAlias('', 5, 'tl_table', 'Existing Alias'));
        $this->assertSame('existing-alias', $util->generateAlias('existing-alias', 1, 'tl_table', 'Existing Alias'));
        $this->assertSame('ich-du-cookies-für-alle', $util->generateAlias('', 6, 'tl_table', 'Ich & du || Cookie\'s für $alle'));
        $this->assertSame('ich-du-cookies-fuer-alle', $util->generateAlias('', 6, 'tl_table', 'Ich & du || Cookie\'s für $alle', false));
        $GLOBALS['TL_LANG']['ERR']['aliasExists'] = 'Alias %s already exist!';
        $this->expectException(\Exception::class);
        $util->generateAlias('existing-alias', 5, 'tl_table', 'Existing Alias');
    }

    public function testAddAuthorFiledAndCallback()
    {
        $array['TL_DCA']['testTable']['config']['oncreate_callback']['setAuthorIDOnCreate'] = ['huh.utils.dca', 'setAuthorIDOnCreate'];
        $array['TL_DCA']['testTable']['config']['onload_callback']['modifyAuthorPaletteOnLoad'] = ['huh.utils.dca', 'modifyAuthorPaletteOnLoad', true];

        $array['TL_DCA']['testTable']['fields']['authorType'] = [
            'label' => &$GLOBALS['TL_LANG']['MSC']['utilsBundle']['authorType'],
            'exclude' => true,
            'filter' => true,
            'default' => 'none',
            'inputType' => 'select',
            'options' => [
                'none',
                'member',
                'user',
            ],
            'reference' => $GLOBALS['TL_LANG']['MSC']['utilsBundle']['authorType'],
            'eval' => ['doNotCopy' => true, 'submitOnChange' => true, 'mandatory' => true, 'tl_class' => 'w50 clr'],
            'sql' => "varchar(255) NOT NULL default 'none'",
        ];

        $array['TL_DCA']['testTable']['fields']['author'] = [
            'label' => &$GLOBALS['TL_LANG']['MSC']['utilsBundle']['author'],
            'exclude' => true,
            'search' => true,
            'filter' => true,
            'inputType' => 'select',
            'options_callback' => function () {
                return \Contao\System::getContainer()->get('huh.utils.choice.model_instance')->getCachedChoices([
                    'dataContainer' => 'tl_member',
                    'labelPattern' => '%firstname% %lastname% (ID %id%)',
                ]);
            },
            'eval' => [
                'doNotCopy' => true,
                'chosen' => true,
                'includeBlankOption' => true,
                'tl_class' => 'w50',
            ],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ];

        $dcaUtil = new DcaUtil($this->mockContaoFramework());
        $dcaUtil->addAuthorFieldAndCallback('testTable');

        $this->assertSame($array['TL_DCA']['testTable']['fields']['author']['label'], $GLOBALS['TL_DCA']['testTable']['fields']['author']['label']);
        $this->assertSame($array['TL_DCA']['testTable']['fields']['author']['eval'], $GLOBALS['TL_DCA']['testTable']['fields']['author']['eval']);
        $this->assertSame($array['TL_DCA']['testTable']['fields']['author']['sql'], $GLOBALS['TL_DCA']['testTable']['fields']['author']['sql']);
        $this->assertSame($array['TL_DCA']['testTable']['fields']['author']['inputType'], $GLOBALS['TL_DCA']['testTable']['fields']['author']['inputType']);
        $this->assertSame($array['TL_DCA']['testTable']['fields']['authorType'], $GLOBALS['TL_DCA']['testTable']['fields']['authorType']);
        $this->assertSame($array['TL_DCA']['testTable']['config']['onload_callback']['modifyAuthorPaletteOnLoad'], $GLOBALS['TL_DCA']['testTable']['config']['onload_callback']['modifyAuthorPaletteOnLoad']);
        $this->assertSame($array['TL_DCA']['testTable']['config']['oncreate_callback']['setAuthorIDOnCreate'], $GLOBALS['TL_DCA']['testTable']['config']['oncreate_callback']['setAuthorIDOnCreate']);
    }

    public function testSetAuthorIDOnCreate()
    {
        $frontendUserModel = $this->mockClassWithProperties(FrontendUser::class, ['id' => 2]);
        $frontendUser = $this->mockAdapter(['getInstance']);
        $frontendUser->method('getInstance')->willReturn($frontendUserModel);
        $dcaUtil = new DcaUtil($this->mockContaoFramework([Database::class => $this->getDatabaseMock(), FrontendUser::class => $frontendUser]));
        $dcaUtil->setAuthorIDOnCreate('table', 2, ['row'], $this->getDataContainerMock());

        $container = System::getContainer();
        $containerUtils = $this->mockAdapter(['isFrontend']);
        $containerUtils->method('isFrontend')->willReturn(false);
        $container->set('huh.utils.container', $containerUtils);
        System::setContainer($container);

        $backendUserModel = $this->mockClassWithProperties(FrontendUser::class, ['id' => 2]);
        $backendUser = $this->mockAdapter(['getInstance']);
        $backendUser->method('getInstance')->willReturn($backendUserModel);
        $dcaUtil = new DcaUtil($this->mockContaoFramework([Database::class => $this->getDatabaseMock(), BackendUser::class => $backendUser]));
        $dcaUtil->setAuthorIDOnCreate('table', 2, ['row'], $this->getDataContainerMock());

        $container = System::getContainer();
        $utilsModel = $this->createMock(ModelUtil::class);
        $utilsModel->method('findModelInstanceByPk')->willReturn(null);
        $container->set('huh.utils.model', $utilsModel);
        System::setContainer($container);

        $result = $dcaUtil->setAuthorIDOnCreate('table', 2, ['row'], $this->getDataContainerMock());
        $this->assertFalse($result);
    }

    public function testModifyAuthorPaletteOnLoad()
    {
        $dcaUtil = new DcaUtil($this->mockContaoFramework());
        $dcaUtil->modifyAuthorPaletteOnLoad($this->getDataContainerMock());

        $this->assertArrayNotHasKey('author', $GLOBALS['TL_DCA']['testTable']['fields']);

        $container = System::getContainer();
        $mockedModel = $this->mockClassWithProperties(Model::class, ['overrideTitle' => 'title', 'title' => 'title', 'author' => null, 'authorType' => 'user']);
        $utilsModel = $this->createMock(ModelUtil::class);
        $utilsModel->method('findModelInstanceByPk')->willReturn($mockedModel);
        $container->set('huh.utils.model', $utilsModel);
        System::setContainer($container);

        $dcaUtil = new DcaUtil($this->mockContaoFramework());
        $dcaUtil->modifyAuthorPaletteOnLoad($this->getDataContainerMock());
        $this->arrayHasKey('options_callback', $GLOBALS['TL_DCA']['testTable']['fields']['author']);

        $container = System::getContainer();
        $utilsModel = $this->createMock(ModelUtil::class);
        $utilsModel->method('findModelInstanceByPk')->willReturn(null);
        $container->set('huh.utils.model', $utilsModel);
        System::setContainer($container);

        $dcaUtil = new DcaUtil($this->mockContaoFramework());
        $result = $dcaUtil->modifyAuthorPaletteOnLoad($this->getDataContainerMock());
        $this->assertFalse($result);
    }

    public function getDatabaseMock()
    {
        $databaseAdapter = $this->mockAdapter([
            'getInstance',
            'prepare',
            'execute',
            'fieldExists',
        ]);
        $databaseAdapter->method('getInstance')->willReturnSelf();
        $databaseAdapter->method('prepare')->withAnyParameters()->willReturnSelf();
        $databaseAdapter->method('fieldExists')->willReturn(true);
        $databaseAdapter->method('execute')->with($this->anything())->willReturnCallback(function ($alias) {
            $result = new \stdClass();
            $result->numRows = 0;
            $result->id = 5;
            switch ($alias) {
                case 'existing-alias':
                    $result->numRows = 1;
                    $result->id = 1;
                    break;
            }

            return $result;
        });

        return $databaseAdapter;
    }

    /**
     * @return DataContainer|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getDataContainerMock()
    {
        return $this->mockClassWithProperties(DataContainer::class, ['id' => 1, 'table' => 'testTable']);
    }
}

<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Driver;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Model;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\UtilsBundle\Driver\DC_Table_Utils;
use HeimrichHannot\UtilsBundle\Model\CfgTagModel;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\RouterInterface;

class DC_Table_UtilsTest extends ContaoTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        if (!\defined('TL_MODE')) {
            \define('TL_MODE', 'BE');
        }

        $container = $this->mockContainer();

        $requestStack = new RequestStack();
        $requestStack->push(new \Symfony\Component\HttpFoundation\Request());

        $backendMatcher = new RequestMatcher('/contao', 'test.com', null, ['192.168.1.0']);
        $frontendMatcher = new RequestMatcher('/index', 'test.com', null, ['192.168.1.0']);

        $scopeMatcher = new ScopeMatcher($backendMatcher, $frontendMatcher);

        $request = new Request($this->mockContaoFramework(), $requestStack, $scopeMatcher);

        $container->set('huh.request', $request);

        $container->set('database_connection', $this->createMock(Connection::class));
        $container->set('request_stack', $this->createRequestStackMock());
        $container->set('router', $this->createRouterMock());
        $container->set('contao.framework', $this->mockContaoFramework());
        $container->set('session', new Session(new MockArraySessionStorage()));

        $dbalAdapter = $this->mockAdapter(['getParams']);
        $dbalAdapter->method('getParams')->willReturn([]);
        $container->set('doctrine.dbal.default_connection', $dbalAdapter);

        $modelUtilsAdapter = $this->mockAdapter(['findModelInstanceByPk']);
        $modelUtilsAdapter->method('findModelInstanceByPk')->willReturn($this->createMock(Model::class));
        $container->set('huh.utils.model', $modelUtilsAdapter);

        System::setContainer($container);

        if (!interface_exists('listable')) {
            include_once __DIR__.'/../../vendor/contao/core-bundle/src/Resources/contao/helper/interface.php';
        }

        if (!\function_exists('standardize')) {
            include_once __DIR__.'/../../vendor/contao/core-bundle/src/Resources/contao/helper/functions.php';
        }
    }

    public function createRequestStackMock()
    {
        $requestStack = new RequestStack();
        $request = new \Symfony\Component\HttpFoundation\Request();
        $request->attributes->set('_contao_referer_id', 'foobar');
        $requestStack->push($request);

        return $requestStack;
    }

    public function createRouterMock()
    {
        $router = $this->createMock(RouterInterface::class);
        $router->method('generate')->with('contao_backend', $this->anything())->willReturnCallback(function ($route, $params = []) {
            $url = '/contao';

            if (!empty($params)) {
                $count = 0;

                foreach ($params as $key => $value) {
                    $url .= (0 === $count ? '?' : '&');
                    $url .= $key.'='.$value;
                    ++$count;
                }
            }

            return $url;
        });

        return $router;
    }

    public function testInstantiation()
    {
        $this->createGlobalDca('table');
        $dcTableUtils = new DC_Table_Utils('table');
        $this->assertInstanceOf(DC_Table_Utils::class, $dcTableUtils);
    }

    public function testCreateFromModel()
    {
        $result = DC_Table_Utils::createFromModel($this->getModel());
        $this->assertInstanceOf(DC_Table_Utils::class, $result);
    }

    public function testCreateFromModelData()
    {
        $result = DC_Table_Utils::createFromModelData(['id' => 12], 'table', 'field');
        $this->assertInstanceOf(DC_Table_Utils::class, $result);
    }

    /**
     * @return Model | \PHPUnit_Framework_MockObject_MockObject
     */
    public function getModel()
    {
        $this->createGlobalDca('tl_cfg_tag');
        $model = new CfgTagModel($this->mockContaoFramework());

        return $model;
    }

    public function createGlobalDca($table)
    {
        $GLOBALS['TL_DCA'][$table] = [
            'config' => [
                'dataContainer' => 'Table',
                'ptable' => 'ptable',
                'ctable' => ['tl_content', 'ctable'],
                'enableVersioning' => true,
                'onsubmit_callback' => [],
                'oncopy_callback' => [],
                'onload_callback' => [],
                'sql' => [
                    'keys' => [
                        'id' => 'primary',
                    ],
                ],
            ],
            'list' => [
                'label' => [
                    'fields' => ['title'],
                    'format' => '%s',
                ],
                'sorting' => [
                    'mode' => 1,
                    'fields' => ['title'],
                    'headerFields' => ['title'],
                    'panelLayout' => 'filter;sort,search,limit',
                    'root' => [],
                ],
                'global_operations' => [
                    'all' => [
                        'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                        'href' => 'act=select',
                        'class' => 'header_edit_all',
                        'attributes' => 'onclick="Backend.getScrollOffset();"',
                    ],
                ],
                'operations' => [
                    'edit' => [
                        'label' => &$GLOBALS['TL_LANG']['table']['edit'],
                        'href' => 'table=tl_content&ptable=table',
                        'icon' => 'edit.gif',
                    ],
                ],
            ],
            'palettes' => [
                'default' => '{general_legend},title;',
            ],

            'subpalettes' => [],
            'fields' => [
                'id' => [
                    'sql' => 'int(10) unsigned NOT NULL auto_increment',
                ],
                'pid' => [
                    'foreignKey' => 'ptable.id',
                    'sql' => "int(10) unsigned NOT NULL default '0'",
                    'relation' => ['type' => 'belongsTo', 'load' => 'eager'],
                ],
                'title' => [
                    'label' => &$GLOBALS['TL_LANG']['table']['title'],
                    'exclude' => true,
                    'search' => true,
                    'inputType' => 'text',
                    'eval' => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true],
                    'sql' => "varchar(255) NOT NULL default ''",
                ],
            ],
        ];
    }
}

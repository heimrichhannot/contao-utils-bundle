<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Url;

use Contao\Controller;
use Contao\Environment;
use Contao\Model;
use Contao\PageModel;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class UrlUtilTest extends ContaoTestCase
{
    public function setUp()
    {
        parent::setUp();

        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = 80;

        Environment::set('requestUri', '?answer=12');

        $container = $this->mockContainer();
        $container->set('contao.framework', $this->mockContaoFramework());
        $container->set('request_stack', $this->createRequestStackMock());

        $jumpToPage = $this->mockClassWithProperties(PageModel::class, ['id' => 2]);
        $utilsModelAdapter = $this->mockAdapter(['findModelInstanceByPk']);
        $utilsModelAdapter->method('findModelInstanceByPk')->willReturn($jumpToPage);
        $container->set('huh.utils.model', $utilsModelAdapter);
        $container->set('router', $this->createRouterMock());
        System::setContainer($container);
        if (!\function_exists('ampersand')) {
            include_once __DIR__.'/../../vendor/contao/core-bundle/src/Resources/contao/helper/functions.php';
        }
    }

    public function testGetCurrentUrl()
    {
        $framework = $this->mockContaoFramework();
        $urlUtil = new UrlUtil($framework);

        $url = $urlUtil->getCurrentUrl(['skipParams' => false]);
        $urlWithoutParams = $urlUtil->getCurrentUrl(['skipParams' => true]);

        $this->assertSame('http://localhost', $urlWithoutParams);
        $this->assertSame('http://localhost?answer=12', $url);
    }

    public function testAddQueryString()
    {
        $framework = $this->mockContaoFramework();
        $urlUtil = new UrlUtil($framework);

        $url = $urlUtil->addQueryString('question=1');
        $this->assertSame('?answer=12&question=1', $url);

        $framework = $this->mockContaoFramework();
        $urlUtil = new UrlUtil($framework);

        $url = $urlUtil->addQueryString('question=1', 'http://localhost');
        $this->assertSame('http://localhost?question=1', $url);
    }

    public function testRemoveQueryString()
    {
        $framework = $this->mockContaoFramework();
        $urlUtil = new UrlUtil($framework);

        $url = $urlUtil->removeQueryString(['answer', 'bla'], 'http://localhost?answer=12&bla=fuuu');
        $this->assertSame('http://localhost', $url);

        $url = $urlUtil->removeQueryString(['answer'], 'http://localhost');
        $this->assertSame('http://localhost', $url);

        $url = $urlUtil->removeQueryString([], 'http://localhost');
        $this->assertSame('http://localhost', $url);

        $url = $urlUtil->removeQueryString(['answer', 'bla'], 'http://localhost?answer=12&blaaa=fuuu');
        $this->assertSame('http://localhost?blaaa=fuuu', $url);
    }

    public function testGetJumpToPageObject()
    {
        $objPage = $this->mockClassWithProperties(Model::class, ['id' => 2]);
        $GLOBALS['objPage'] = $objPage;

        $framework = $this->mockContaoFramework();
        $urlUtil = new UrlUtil($framework);

        $jumpToPage = $urlUtil->getJumpToPageObject(12);

        $this->assertInstanceOf(PageModel::class, $jumpToPage);

        $container = System::getContainer();
        $utilsModelAdapter = $this->mockAdapter(['findModelInstanceByPk']);
        $utilsModelAdapter->method('findModelInstanceByPk')->willReturn(null);
        $container->set('huh.utils.model', $utilsModelAdapter);
        System::setContainer($container);

        $framework = $this->mockContaoFramework();
        $urlUtil = new UrlUtil($framework);

        $jumpToPage = $urlUtil->getJumpToPageObject(12);
        $this->assertSame($objPage, $jumpToPage);

        $jumpToPage = $urlUtil->getJumpToPageObject(12, false);
        $this->assertNull($jumpToPage);
    }

    public function testPrepareUrl()
    {
        if (!defined('TL_MODE')) {
            define('TL_MODE', 'BE');
        }
        $pageModel = $this->createMock(PageModel::class);
        $pageModel->method('row')->willReturn(['id' => 1, 'rootId' => 12, 'alias' => 'alias']);

        $pageModelAdapter = $this->mockAdapter(['findByPk']);
        $pageModelAdapter->method('findByPk')->willReturn(null);
        $urlUtil = new UrlUtil($this->mockContaoFramework([PageModel::class => $pageModelAdapter]));
        try {
            $url = $urlUtil->removeQueryString([], 1);
        } catch (\Exception $exception) {
            $this->assertSame('Given page id does not exist.', $exception->getMessage());
        }

        $controllerAdapter = $this->mockAdapter(['generateFrontendUrl']);
        $controllerAdapter->method('generateFrontendUrl')->willReturn('www.localhost.de/page');
        $pageModelAdapter = $this->mockAdapter(['findByPk']);
        $pageModelAdapter->method('findByPk')->willReturn($pageModel);
        $urlUtil = new UrlUtil($this->mockContaoFramework([PageModel::class => $pageModelAdapter, Controller::class => $controllerAdapter]));
        $url = $urlUtil->removeQueryString([], 1);
        $this->assertSame('www.localhost.de/page?answer=12', $url);
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
        $router->method('generate')->with('contao_backend', $this->anything())->will($this->returnCallback(function ($route, $params = []) {
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
        }));

        return $router;
    }
}

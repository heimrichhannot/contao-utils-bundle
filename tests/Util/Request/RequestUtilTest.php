<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Util\Request;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;
use HeimrichHannot\TestUtilitiesBundle\Mock\ModelMockTrait;
use HeimrichHannot\UtilsBundle\Tests\AbstractUtilsTestCase;
use HeimrichHannot\UtilsBundle\Util\ModelUtil;
use HeimrichHannot\UtilsBundle\Util\Request\RequestUtil;
use PHPUnit\Framework\MockObject\MockBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestUtilTest extends AbstractUtilsTestCase
{
    use ModelMockTrait;

    /**
     * @param array{
     *     requestStack?: RequestStack,
     *     contaoFramework?: ContaoFramework,
     * } $parameters
     * @param MockBuilder|null $mockBuilder
     * @return RequestUtil
     */
    public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null)
    {
        $requestStack = $parameters['requestStack'] ?? $this->createMock(RequestStack::class);
        $contaoFramework = $parameters['contaoFramework'] ?? $this->mockContaoFramework([
            PageModel::class => $this->mockAdapter(['findByPk']),
        ]);

        return new RequestUtil($requestStack, $contaoFramework);
    }

    public function testGetCurrentPageModel()
    {
        unset($GLOBALS['objPage']);
        $requestStack = new RequestStack();
        $instance = $this->getTestInstance(['requestStack' => $requestStack]);
        $this->assertNull($instance->getCurrentPageModel());

        $requestStack = new RequestStack();
        $requestStack->push(new Request());
        $instance = $this->getTestInstance(['requestStack' => $requestStack]);
        $this->assertNull($instance->getCurrentPageModel());

        $pageModel = $this->mockModelObject(PageModel::class, ['id' => 5]);
        $requestStack = new RequestStack();
        $request = new Request([], [], ['pageModel' => $pageModel]);
        $requestStack->push($request);
        $instance = $this->getTestInstance(['requestStack' => $requestStack]);
        $this->assertSame($pageModel, $instance->getCurrentPageModel());

        $pageModel = $this->mockModelObject(PageModel::class, ['id' => 5]);
        $requestStack = new RequestStack();
        $request = new Request([], [], ['pageModel' => $pageModel->id]);
        $requestStack->push($request);
        $GLOBALS['objPage'] = $pageModel;
        $instance = $this->getTestInstance(['requestStack' => $requestStack]);
        $this->assertSame($pageModel, $instance->getCurrentPageModel());

        unset($GLOBALS['objPage']);
        $pageModelAdapter = $this->mockAdapter(['findByPk']);
        $pageModelAdapter->method('findByPk')->willReturnCallback(function ($id) {
            return $this->mockModelObject(PageModel::class, ['id' => $id]);
        });
        $framework = $this->mockContaoFramework([
            PageModel::class => $pageModelAdapter,
        ]);
        $requestStack = new RequestStack();
        $request = new Request([], [], ['pageModel' => 5]);
        $requestStack->push($request);
        $instance = $this->getTestInstance([
            'requestStack' => $requestStack,
            'contaoFramework' => $framework,
        ]);
        $this->assertSame(5, $instance->getCurrentPageModel()->id);
    }

    public function testGetCurrentRootPageModel()
    {
        $instance = $this->getTestInstance();
        $this->assertNull($instance->getCurrentRootPageModel());

        $pageModel = $this->mockModelObject(PageModel::class, ['id' => 5, 'rootId' => 3]);
        $pageModel->expects($this->once())->method('loadDetails');
        $requestStack = new RequestStack();
        $request = new Request([], [], ['pageModel' => $pageModel]);
        $requestStack->push($request);
        $instance = $this->getTestInstance([
            'requestStack' => $requestStack,
        ]);
        $this->assertNull($instance->getCurrentRootPageModel());

        $pageModel = $this->mockModelObject(PageModel::class, ['id' => 5, 'rootId' => 3]);
        $pageModelAdapter = $this->mockAdapter(['findByPk']);
        $pageModelAdapter->method('findByPk')->willReturnCallback(function ($id) {
            return $this->mockModelObject(PageModel::class, ['id' => $id]);
        });
        $framework = $this->mockContaoFramework([
            PageModel::class => $pageModelAdapter,
        ]);
        $requestStack = new RequestStack();
        $request = new Request([], [], ['pageModel' => $pageModel]);
        $requestStack->push($request);
        $instance = $this->getTestInstance([
            'requestStack' => $requestStack,
            'contaoFramework' => $framework,
        ]);
        $this->assertSame(3, $instance->getCurrentRootPageModel()->id);
    }

    public function testGetBaseUrl()
    {
        $instance = $this->getTestInstance();
        $this->assertEmpty($instance->getBaseUrl());

        $requestStack = new RequestStack();
        $request = Request::create('http://example.org');
        $requestStack->push($request);
        $instance = $this->getTestInstance(['requestStack' => $requestStack]);
        $this->assertSame('http://example.org', $instance->getBaseUrl());

        $requestStack = new RequestStack();
        $request = Request::create('http://example.org/de/privacy');
        $requestStack->push($request);
        $instance = $this->getTestInstance(['requestStack' => $requestStack]);
        $this->assertSame('http://example.org', $instance->getBaseUrl());

        $requestStack = new RequestStack();
        $instance = $this->getTestInstance(['requestStack' => $requestStack]);
        $pageModel = $this->mockModelObject(PageModel::class);
        $pageModel->method('getAbsoluteUrl')->willReturn('example.org/de/news');
        $this->assertSame('http://example.org', $instance->getBaseUrl(['pageModel' => $pageModel]));

        $this->assertSame('https://heimrich-hannot.com', $instance->getBaseUrl(['fallback' => 'https://heimrich-hannot.com']));

        $this->expectException(\Exception::class);
        $instance->getBaseUrl([], ['throwException' => true]);
    }

    public function testIsNewVisitor()
    {
        $requestStack = new RequestStack();
        $request = Request::create('http://example.org');
        $request->headers->set('referer', 'http://example.org');
        $requestStack->push($request);
        $instance = $this->getTestInstance(['requestStack' => $requestStack]);
        $this->assertFalse($instance->isNewVisitor());

        $request->headers->set('referer', 'http://heimrich-hannot.de');
        $this->assertTrue($instance->isNewVisitor());

        $request->headers->remove('referer');
        $this->assertTrue($instance->isNewVisitor());
    }

    public function testIsIndexPage()
    {
        $pageModelAdapter = $this->mockAdapter(['findFirstPublishedByPid']);
        $pageModelAdapter->method('findFirstPublishedByPid')->willReturn($this->mockModelObject(PageModel::class, ['id' => 2]));
        $framework = $this->mockContaoFramework([
            PageModel::class => $pageModelAdapter,
        ]);

        $instance = $this->getTestInstance(['contaoFramework' => $framework]);

        $this->assertFalse($instance->isIndexPage());
        $this->assertFalse($instance->isIndexPage($this->mockModelObject(PageModel::class, ['id' => 3, 'pid' => 1])));
        $this->assertFalse($instance->isIndexPage($this->mockModelObject(PageModel::class, ['id' => 2, 'pid' => 1])));

        $request = Request::create('https://example.org');
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $instance = $this->getTestInstance([
            'contaoFramework' => $framework,
            'requestStack' => $requestStack,
        ]);

        $this->assertFalse($instance->isIndexPage($this->mockModelObject(PageModel::class, ['id' => 3, 'pid' => 1])));
        $this->assertTrue($instance->isIndexPage($this->mockModelObject(PageModel::class, ['id' => 2, 'pid' => 1])));

        $request = Request::create('https://example.org', 'GET', ['auto_item' => 'example-page']);
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $instance = $this->getTestInstance([
            'contaoFramework' => $framework,
            'requestStack' => $requestStack,
        ]);

        $this->assertFalse($instance->isIndexPage($this->mockModelObject(PageModel::class, ['id' => 2, 'pid' => 1])));
    }
}

<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Util\Request;

use Contao\PageModel;
use HeimrichHannot\TestUtilitiesBundle\Mock\ModelMockTrait;
use HeimrichHannot\UtilsBundle\Tests\AbstractUtilsTestCase;
use HeimrichHannot\UtilsBundle\Util\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Util\Request\RequestUtil;
use PHPUnit\Framework\MockObject\MockBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestUtilTest extends AbstractUtilsTestCase
{
    use ModelMockTrait;

    public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null)
    {
        $modelUtil = $parameters['modelUtil'] ?? $this->createMock(ModelUtil::class);
        $requestStack = $parameters['requestStack'] ?? $this->createMock(RequestStack::class);
        $kernelPackages = $parameters['kernelPackages'] ?? [];
        $contaoFramework = $parameters['contaoFramework'] ?? $this->mockContaoFramework();

        return new RequestUtil($modelUtil, $requestStack, $kernelPackages, $contaoFramework);
    }

    public function testGetCurrentPageModel()
    {
        unset($GLOBALS['objPage']);

        $instance = $this->getTestInstance();
        $this->assertNull($instance->getCurrentPageModel());

        $instance = $this->getTestInstance([
            'kernelPackages' => ['contao/core-bundle' => '4.4.46'],
        ]);
        $this->assertNull($instance->getCurrentPageModel());

        $instance = $this->getTestInstance([
            'kernelPackages' => ['contao/core-bundle' => '4.8.5'],
        ]);
        $this->assertNull($instance->getCurrentPageModel());

        $instance = $this->getTestInstance([
            'kernelPackages' => ['contao/core-bundle' => '4.9.5'],
        ]);
        $this->assertNull($instance->getCurrentPageModel());

        $requestStack = new RequestStack();
        $requestStack->push(new Request());
        $instance = $this->getTestInstance([
            'requestStack' => $requestStack,
            'kernelPackages' => ['contao/core-bundle' => '4.9.5'],
        ]);
        $this->assertNull($instance->getCurrentPageModel());

        $requestStack = new RequestStack();
        $request = new Request([], [], ['pageModel' => 5]);
        $requestStack->push($request);

        $instance = $this->getTestInstance([
            'requestStack' => $requestStack,
            'kernelPackages' => ['contao/core-bundle' => '4.9.5'],
        ]);
        $this->assertNull($instance->getCurrentPageModel());

        $pageModel = $this->mockModelObject(PageModel::class, ['id' => 5]);
        $requestStack = new RequestStack();
        $request = new Request([], [], ['pageModel' => $pageModel]);
        $requestStack->push($request);
        $instance = $this->getTestInstance([
            'requestStack' => $requestStack,
            'kernelPackages' => ['contao/core-bundle' => '4.9.5'],
        ]);
        $this->assertSame(5, $instance->getCurrentPageModel()->id);

        $GLOBALS['objPage'] = $pageModel;
        $requestStack = new RequestStack();
        $request = new Request([], [], ['pageModel' => 5]);
        $requestStack->push($request);

        $instance = $this->getTestInstance([
            'requestStack' => $requestStack,
            'kernelPackages' => ['contao/core-bundle' => '4.9.5'],
        ]);
        $this->assertSame(5, $instance->getCurrentPageModel()->id);

        $instance = $this->getTestInstance([
            'requestStack' => $requestStack,
            'kernelPackages' => ['contao/core-bundle' => '4.4.45'],
        ]);
        $this->assertSame(5, $instance->getCurrentPageModel()->id);

        unset($GLOBALS['objPage']);

        $modelUtil = $this->createMock(ModelUtil::class);
        $modelUtil->method('findModelInstanceByPk')->willReturn($pageModel);
        $requestStack = new RequestStack();
        $request = new Request([], [], ['pageModel' => 5]);
        $requestStack->push($request);

        $instance = $this->getTestInstance([
            'modelUtil' => $modelUtil,
            'requestStack' => $requestStack,
            'kernelPackages' => ['contao/core-bundle' => '4.9.5'],
        ]);
        $this->assertSame(5, $instance->getCurrentPageModel()->id);
    }

    public function testGetCurrentRootPageModel()
    {
        $modelUtil = $this->createMock(ModelUtil::class);
        $modelUtil->method('findModelInstanceByPk')->willReturn(null);
        $requestUtil = $this->getTestInstance([
            'modelUtil' => $modelUtil,
        ]);
        $this->assertNull($requestUtil->getCurrentPageModel());

        $pageModel = $this->mockModelObject(PageModel::class, ['id' => 5, 'rootId' => 3]);
        $rootPageModel = $this->mockModelObject(PageModel::class, ['id' => 3, 'rootId' => 3]);
        $modelUtil = $this->createMock(ModelUtil::class);
        $modelUtil->method('findModelInstanceByPk')->willReturnCallback(function ($table, $id) use ($rootPageModel) {
            switch ($id) {
                case 3:
                    return $rootPageModel;
            }

            return null;
        });
        $requestStack = new RequestStack();
        $request = new Request([], [], ['pageModel' => $pageModel]);
        $requestStack->push($request);
        $requestUtil = $this->getTestInstance([
            'requestStack' => $requestStack,
            'kernelPackages' => ['contao/core-bundle' => '4.9.5'],
            'modelUtil' => $modelUtil,
        ]);

        $this->assertSame(3, $requestUtil->getCurrentRootPageModel()->id);

        unset($GLOBALS['objPage']);
        $requestUtil = $this->getTestInstance([
            'kernelPackages' => ['contao/core-bundle' => '4.4.5'],
        ]);
        $this->assertNull($requestUtil->getCurrentPageModel());
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

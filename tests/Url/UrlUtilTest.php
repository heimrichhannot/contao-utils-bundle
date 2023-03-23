<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Url;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Environment;
use Contao\Model;
use Contao\PageModel;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Exception\InvalidUrlException;
use HeimrichHannot\UtilsBundle\Request\RequestUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class UrlUtilTest extends ContaoTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = 80;

        Environment::set('requestUri', '?answer=12');

        $container = $this->getContainerWithContaoConfiguration();
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

    public function createTestInstance(array $parameter = [])
    {
        if (!isset($parameter['framework'])) {
            $parameter['framework'] = $this->mockContaoFramework();
        }

        $requestStack = $parameter['requestStack'] ?? $this->createMock(RequestStack::class);

        /** @var RequestUtil|MockObject $requestUtil */
        $requestUtil = $this->createMock(RequestUtil::class);

        $instance = new UrlUtil($parameter['framework'], $requestUtil, $requestStack);

        return $instance;
    }

    public function testGetCurrentUrl()
    {
        $urlUtil = $this->createTestInstance();

        $url = $urlUtil->getCurrentUrl();
        $url2 = $urlUtil->getCurrentUrl(['skipParams' => false]);
        $urlWithoutParams = $urlUtil->getCurrentUrl(['skipParams' => true]);

        $this->assertSame('http://localhost', $urlWithoutParams);
        $this->assertSame('http://localhost?answer=12', $url);
        $this->assertSame('http://localhost?answer=12', $url2);
    }

    public function testGetJumpToPageObject()
    {
        $objPage = $this->mockClassWithProperties(Model::class, ['id' => 2]);
        $GLOBALS['objPage'] = $objPage;

        $urlUtil = $this->createTestInstance();

        $jumpToPage = $urlUtil->getJumpToPageObject(12);

        $this->assertInstanceOf(PageModel::class, $jumpToPage);

        $container = System::getContainer();
        $utilsModelAdapter = $this->mockAdapter(['findModelInstanceByPk']);
        $utilsModelAdapter->method('findModelInstanceByPk')->willReturn(null);
        $container->set('huh.utils.model', $utilsModelAdapter);
        System::setContainer($container);

        $urlUtil = $this->createTestInstance();

        $jumpToPage = $urlUtil->getJumpToPageObject(12);
        $this->assertSame($objPage, $jumpToPage);

        $jumpToPage = $urlUtil->getJumpToPageObject(12, false);
        $this->assertNull($jumpToPage);
    }

    /**
     * Test redirect() when headers_sent() is true.
     */
    public function testRedirectHeadersAlreadySent()
    {
        $backendMatcher = new RequestMatcher('/contao', 'test.com', null, ['192.168.1.0']);
        $frontendMatcher = new RequestMatcher('/index', 'test.com', null, ['192.168.1.0']);

        $scopeMatcher = new ScopeMatcher($backendMatcher, $frontendMatcher);

        $request = new Request();

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $urlUtil = $this->createTestInstance(['requestStack' => $requestStack]);
        $this->assertSame(UrlUtil::TERMINATE_HEADERS_ALREADY_SENT, $urlUtil->redirect('/test?foo=bar&amp;test=123', 301, true));
    }

    /**
     * Test 301 redirect() html &amp; in url.
     */
    public function test301RedirectWithHtmlAmpersandParams()
    {
        $backendMatcher = new RequestMatcher('/contao', 'test.com', null, ['192.168.1.0']);
        $frontendMatcher = new RequestMatcher('/index', 'test.com', null, ['192.168.1.0']);

        $scopeMatcher = new ScopeMatcher($backendMatcher, $frontendMatcher);

        $request = new Request();

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $urlUtil = $this->createTestInstance([
            'requestStack' => $requestStack
        ]);
        $headers = $urlUtil->redirect('/test?foo=bar&amp;test=123', 301, true, true);
        $this->assertNotEmpty($headers);
        $this->assertSame(['HTTP/1.1 301 Moved Permanently', 'Location: http://localhost/test?foo=bar&test=123'], $headers);
    }

    /**
     * Test 302 redirect().
     */
    public function test302Redirect()
    {
        $backendMatcher = new RequestMatcher('/contao', 'test.com', null, ['192.168.1.0']);
        $frontendMatcher = new RequestMatcher('/index', 'test.com', null, ['192.168.1.0']);

        $scopeMatcher = new ScopeMatcher($backendMatcher, $frontendMatcher);

        $request = new Request();

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $urlUtil = $this->createTestInstance([
            'requestStack' => $requestStack
        ]);
        $headers = $urlUtil->redirect('http://test.com/test?foo=bar', 302, true, true);
        $this->assertNotEmpty($headers);
        $this->assertSame(['HTTP/1.1 302 Found', 'Location: http://test.com/test?foo=bar'], $headers);
    }

    /**
     * Test 303 redirect().
     */
    public function test303Redirect()
    {
        $backendMatcher = new RequestMatcher('/contao', 'test.com', null, ['192.168.1.0']);
        $frontendMatcher = new RequestMatcher('/index', 'test.com', null, ['192.168.1.0']);

        $scopeMatcher = new ScopeMatcher($backendMatcher, $frontendMatcher);

        $request = new Request();

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $urlUtil = $this->createTestInstance([
            'requestStack' => $requestStack
        ]);
        $headers = $urlUtil->redirect('http://test.com/test?foo=bar', 303, true, true);
        $this->assertNotEmpty($headers);
        $this->assertSame(['HTTP/1.1 303 See Other', 'Location: http://test.com/test?foo=bar'], $headers);
    }

    /**
     * Test 307 redirect().
     */
    public function test307Redirect()
    {
        $backendMatcher = new RequestMatcher('/contao', 'test.com', null, ['192.168.1.0']);
        $frontendMatcher = new RequestMatcher('/index', 'test.com', null, ['192.168.1.0']);

        $scopeMatcher = new ScopeMatcher($backendMatcher, $frontendMatcher);

        $request = new Request();

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $urlUtil = $this->createTestInstance([
            'requestStack' => $requestStack
        ]);
        $headers = $urlUtil->redirect('http://test.com/test?foo=bar', 307, true, true);
        $this->assertNotEmpty($headers);
        $this->assertSame(['HTTP/1.1 307 Temporary Redirect', 'Location: http://test.com/test?foo=bar'], $headers);
    }

    /**
     * Test xhr/ajax redirect().
     */
    public function testXhrRedirect()
    {
        $backendMatcher = new RequestMatcher('/contao', 'test.com', null, ['192.168.1.0']);
        $frontendMatcher = new RequestMatcher('/index', 'test.com', null, ['192.168.1.0']);

        $scopeMatcher = new ScopeMatcher($backendMatcher, $frontendMatcher);

        $request = new Request([], [], [], [], [], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $urlUtil = $this->createTestInstance([
            'requestStack' => $requestStack
        ]);
        $headers = $urlUtil->redirect('http://test.com/test?foo=bar', 307, true, true);
        $this->assertNotEmpty($headers);
        $this->assertSame(['HTTP/1.1 204 No Content', 'X-Ajax-Location: http://test.com/test?foo=bar'], $headers);
    }

    public function createRequestStackMock()
    {
        $requestStack = new RequestStack();
        $request = new Request();
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

    public function testGetRelativePath()
    {
        $instance = $this->createTestInstance();
        $this->assertSame('/de', $instance->getRelativePath('https://example.org/de'));
        $this->assertSame('/pfad?argument=wert#textanker', $instance->getRelativePath('http://benutzername:passwort@hostname:9090/pfad?argument=wert#textanker'));
        $this->assertSame('/path?googleguy=googley', $instance->getRelativePath('//www.example.com/path?googleguy=googley'));
        $this->assertSame('/path?test=1&foo=bar&heimrich=hannot', $instance->getRelativePath('//www.example.com/path?test=1&foo=bar&heimrich=hannot'));
        $this->assertSame('/mypath/myfile.php', $instance->getRelativePath('foobar.com:80/mypath/myfile.php'));

        $exception = null;

        try {
            $instance->getRelativePath('http:///example.com');
        } catch (\Exception $e) {
            $exception = $e;
        }
        $this->assertInstanceOf(InvalidUrlException::class, $exception);

        $instance = $this->createTestInstance();
        $this->assertSame('de', $instance->getRelativePath('https://example.org/de', ['removeLeadingSlash' => true]));
        $this->assertSame('pfad?argument=wert#textanker', $instance->getRelativePath('http://benutzername:passwort@hostname:9090/pfad?argument=wert#textanker', ['removeLeadingSlash' => true]));
        $this->assertSame('path?googleguy=googley', $instance->getRelativePath('//www.example.com/path?googleguy=googley', ['removeLeadingSlash' => true]));
        $this->assertSame('path?test=1&foo=bar&heimrich=hannot', $instance->getRelativePath('//www.example.com/path?test=1&foo=bar&heimrich=hannot', ['removeLeadingSlash' => true]));
        $this->assertSame('mypath/myfile.php', $instance->getRelativePath('foobar.com:80/mypath/myfile.php', ['removeLeadingSlash' => true]));
    }
}

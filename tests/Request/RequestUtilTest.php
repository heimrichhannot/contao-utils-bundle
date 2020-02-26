<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Request;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Request\RequestUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestUtilTest extends ContaoTestCase
{
    public function createTestInstance(array $parameter = [])
    {
        $requestStack = new RequestStack();

        if (!isset($parameter['url'])) {
            $parameter['url'] = 'http://example.org';
        }
        $request = Request::create($parameter['url']);

        if (isset($parameter['referer'])) {
            $request->headers->set('referer', $parameter['referer']);
        }
        $requestStack->push($request);

        $instance = new RequestUtil($requestStack);

        return $instance;
    }

    public function testIsNewVisitor()
    {
        $this->assertFalse($this->createTestInstance(['referer' => 'http://example.org', 'url' => 'http://example.org'])->isNewVisitor());
        $this->assertTrue($this->createTestInstance(['referer' => 'http://heimrich-hannot.de', 'url' => 'http://example.org'])->isNewVisitor());
        $this->assertTrue($this->createTestInstance(['referer' => null, 'url' => 'http://example.org'])->isNewVisitor());
    }
}

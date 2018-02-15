<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Log\Logger;

abstract class TestCaseEnvironment extends ContaoTestCase
{
    public function setUp()
    {
        parent::setUp();

        $container = $this->mockContainer();
        $container->set('request_stack', $this->createRequestStackMock());

        $logger = new Logger();
        $container->set('monolog.logger.contao', $logger);
        System::setContainer($container);
    }

    public function createRequestStackMock()
    {
        $requestStack = new RequestStack();
        $request = new \Symfony\Component\HttpFoundation\Request();
        $request->attributes->set('_contao_referer_id', 'foobar');
        $requestStack->push($request);

        return $requestStack;
    }
}

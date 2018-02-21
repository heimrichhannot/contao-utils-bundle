<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Log\Logger;

abstract class TestCaseEnvironment extends ContaoTestCase
{
    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $fs = new Filesystem();

        if ($fs->exists(TL_ROOT.'/tmp')) {
            $fs->remove(TL_ROOT.'/tmp');
        }
    }

    public function setUp()
    {
        parent::setUp();

        $container = $this->mockContainer();
        $container->set('request_stack', $this->createRequestStackMock());
        $container->setParameter('contao.resources_paths', [__DIR__.'/../vendor/contao/core-bundle/src/Resources/contao']);
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

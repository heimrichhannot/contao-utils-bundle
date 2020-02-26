<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Log\Logger;
use Symfony\Component\Routing\RouterInterface;

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

        if (!\defined('TL_ROOT')) {
            \define('TL_ROOT', sys_get_temp_dir());
        }

        $GLOBALS['TL_CONFIG']['uploadPath'] = sys_get_temp_dir();

        $container = $this->mockContainer();
        $container->set('request_stack', $this->createRequestStackMock());
        $container->setParameter('contao.resources_paths', [__DIR__.'/../vendor/contao/core-bundle/src/Resources/contao']);
        $container->setParameter('contao.image.target_dir', __DIR__.'/../vendor/contao/core-bundle/src/Resources/contao');
        $logger = new Logger();
        $container->set('contao.framework', $this->mockContaoFramework());
        $container->set('monolog.logger.contao', $logger);
        $container->set('session', new Session(new MockArraySessionStorage()));
        $container->set('router', $this->createRouterMock());
        $container->set('database_connection', $this->createMock(Connection::class));
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

    public function createRouterMock()
    {
        $router = $this->createMock(RouterInterface::class);
        $router->method('generate')->with('contao_backend', $this->anything())->willReturnCallback(
                function ($route, $params = []) {
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
                }
        );

        return $router;
    }
}

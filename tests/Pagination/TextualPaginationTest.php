<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Pagination;

use Contao\CoreBundle\Config\ResourceFinder;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Pagination\TextualPagination;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Translation\Translator;

class TextualPaginationTest extends ContaoTestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;
    /**
     * @var Kernel
     */
    private $kernel;

    protected function setUp()
    {
        parent::setUp();

        $GLOBALS['TL_LANGUAGE'] = 'en';
        $GLOBALS['TL_LANG']['MSC']['first'] = '&amp;#171; First';
        $GLOBALS['TL_LANG']['MSC']['previous'] = 'Previous';
        $GLOBALS['TL_LANG']['MSC']['next'] = 'Next';
        $GLOBALS['TL_LANG']['MSC']['last'] = 'Last &amp;#187;';
        $GLOBALS['TL_LANG']['MSC']['totalPages'] = 'Page %s of %s';
        $GLOBALS['TL_LANG']['MSC']['readOnSinglePage'] = 'Read on one page';
        $GLOBALS['TL_LANG']['MSC']['goToPage'] = 'Go to page %s';

        $finder = new ResourceFinder(([
            $this->getFixturesDir().'/vendor/contao/core-bundle/Resources/contao',
        ]));
        $this->container = $this->mockContainer();
        $this->container->set('contao.resource_finder', $finder);
        $this->container->setParameter('kernel.debug', true);
        $this->container->setParameter('kernel.default_locale', 'de');
        $this->container->set('translator', new Translator('en'));
        $this->kernel = $this->createMock(Kernel::class);
        $this->kernel->method('getContainer')->willReturn($this->container);
        $this->container->set('kernel', $this->kernel);

        if (!\function_exists('ampersand')) {
            include_once __DIR__.'/../../vendor/contao/core-bundle/src/Resources/contao/helper/functions.php';
        }
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        System::setContainer($this->container);

        $pagination = new TextualPagination([], '/test', 10, 10);
        $this->assertInstanceOf('HeimrichHannot\UtilsBundle\Pagination\TextualPagination', $pagination);
    }

    /**
     * Test getItemsAsArray() without teasers.
     */
    public function testGetItemsAsArrayWithoutTeasers()
    {
        System::setContainer($this->container);

        $pagination = new TextualPagination([], '', 10, 10);
        $this->assertEmpty($pagination->getItemsAsArray());
    }

    /**
     * Test getItemsAsArray() without teasers but single page url.
     */
    public function testGetItemsAsArrayWithoutTeasersAndSinglePageUrl()
    {
        System::setContainer($this->container);

        $pagination = new TextualPagination([], '/test', 10, 10);
        $result = $pagination->getItemsAsArray();
        $this->assertNotEmpty($result);
        $this->assertSame([
            [
                'page' => 'singlePage',
                'href' => '/test',
                'title' => null,
                'text' => 'Read on one page',
            ],
        ], $result);
    }

    /**
     * Test getItemsAsArray() on first page.
     */
    public function testGetItemsOnFirstPage()
    {
        System::setContainer($this->container);

        $pagination = new TextualPagination([
            1 => 'Teaser page 1',
            2 => 'Teaser page 2',
            3 => 'Teaser page 3',
            4 => 'Teaser page 4',
            5 => 'Teaser page 5',
        ], '/test', 10, 2);
        $result = $pagination->getItemsAsArray();
        $this->assertNotEmpty($result);

        $this->assertSame([
            [
                'page' => 1,
                'href' => null,
                'title' => null,
                'text' => 'Teaser page 1',
            ],
            [
                'page' => 2,
                'href' => '?page=2',
                'title' => 'Go to page 2',
                'text' => 'Teaser page 2',
            ],
            [
                'page' => 3,
                'href' => '?page=3',
                'title' => 'Go to page 3',
                'text' => 'Teaser page 3',
            ],
            [
                'page' => 4,
                'href' => '?page=4',
                'title' => 'Go to page 4',
                'text' => 'Teaser page 4',
            ],
            [
                'page' => 5,
                'href' => '?page=5',
                'title' => 'Go to page 5',
                'text' => 'Teaser page 5',
            ],
            [
                'page' => 'singlePage',
                'href' => '/test',
                'title' => null,
                'text' => 'Read on one page',
            ],
        ], $result);
    }

    protected function getFixturesDir(): string
    {
        return __DIR__.\DIRECTORY_SEPARATOR.'../..'.\DIRECTORY_SEPARATOR.'Fixtures';
    }
}

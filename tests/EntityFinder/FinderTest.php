<?php

namespace HeimrichHannot\UtilsBundle\Tests\EntityFinder;

use Contao\Controller;
use HeimrichHannot\UtilsBundle\EntityFinder\Element;
use HeimrichHannot\UtilsBundle\EntityFinder\EntityFinderHelper;
use HeimrichHannot\UtilsBundle\EntityFinder\Finder;
use HeimrichHannot\UtilsBundle\Tests\AbstractUtilsTestCase;
use PHPUnit\Framework\MockObject\MockBuilder;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class FinderTest extends AbstractUtilsTestCase
{

    public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null)
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->method('dispatch')->willReturnArgument(0);

        $contaoFramework = $this->mockContaoFramework([
            Controller::class => $this->mockAdapter(['loadDataContainer']),
        ]);

        return new Finder(
            $parameters['helper'] ?? $this->createMock(EntityFinderHelper::class),
            $parameters['eventDispatcher'] ?? $eventDispatcher,
            $parameters['framework'] ?? $contaoFramework
        );
    }

    public function testFind()
    {
        $finder = $this->getTestInstance();
        $this->assertNull($finder->find('find', 1));
        $this->assertNull($finder->find('tl_custom', 1));

        $element = new \stdClass();
        $element->id = 1;

        $helper = $this->createMock(EntityFinderHelper::class);
        $helper->method('fetchModelOrData')->willReturn($element);
        $finder = $this->getTestInstance(['helper' => $helper]);
        $entity = $finder->find('tl_custom', 1);
        $this->assertInstanceOf(Element::class, $entity);

    }
}
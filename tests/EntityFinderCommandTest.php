<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests;

use Doctrine\DBAL\Connection;
use HeimrichHannot\UtilsBundle\Command\EntityFinderCommand;
use PHPUnit\Framework\MockObject\MockBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EntityFinderCommandTest extends AbstractUtilsTestCase
{
    public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null)
    {
        $contaoFramework = $parameters['contaoFramework'] ?? $this->mockContaoFramework();
        $eventDispatcher = $parameters['eventDispatcher'] ?? $this->createMock(EventDispatcherInterface::class);
        $connection = $parameters['connection'] ?? $this->createMock(Connection::class);

        return new EntityFinderCommand($contaoFramework, $eventDispatcher, $connection);
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf(EntityFinderCommand::class, $this->getTestInstance());
    }
}

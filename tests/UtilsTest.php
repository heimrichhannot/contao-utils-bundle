<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Utils;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class UtilsTest extends ContaoTestCase
{
    public function createInstance()
    {
        $container = new ContainerBuilder();
        $container->reg(Utils::getSubscribedServices());
        $instance = new Utils($container);

        return $instance;
    }

    public function testGetUtil()
    {
        $instance = $this->createInstance();
    }
}

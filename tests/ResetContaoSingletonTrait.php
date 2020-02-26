<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests;

use Contao\Files;
use Contao\System;
use Symfony\Component\DependencyInjection\ContainerBuilder;

trait ResetContaoSingletonTrait
{
    /**
     * Reset the contao Files singleton.
     *
     * @throws \ReflectionException
     */
    protected function resetFilesInstance(ContainerBuilder $container)
    {
        $filesReflection = new \ReflectionClass(Files::class);
        $instanceProperty = $filesReflection->getProperty('objInstance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);
        System::setContainer($container);
        Files::getInstance();
    }
}

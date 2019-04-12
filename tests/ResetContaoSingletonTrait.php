<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\UtilsBundle\Tests;


use Contao\Files;
use Contao\System;
use Symfony\Component\DependencyInjection\ContainerBuilder;

trait ResetContaoSingletonTrait
{
    /**
     * Reset the contao Files singleton
     *
     * @param ContainerBuilder $container
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
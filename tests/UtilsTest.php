<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\UtilsBundle\Tests;


use Contao\CoreBundle\Tests\TestCase;
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
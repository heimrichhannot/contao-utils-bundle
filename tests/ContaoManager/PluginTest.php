<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\DelegatingParser;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Accordion\AccordionUtil;
use HeimrichHannot\UtilsBundle\Arrays\ArrayUtil;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\ContaoManager\Plugin;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\HeimrichHannotContaoUtilsBundle;
use HeimrichHannot\UtilsBundle\Image\ImageUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Template\TemplateUtil;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\HttpKernel\Kernel;

class PluginTest extends ContaoTestCase
{
    /**
     * @var Plugin
     */
    protected $plugin;
    /**
     * @var ContainerBuilder
     */
    protected $container;
    /**
     * @var string
     */
    protected $projectDir;

    public function testGetBundles()
    {
        $plugin = new Plugin();

        /** @var BundleConfig[] $bundles */
        $bundles = $plugin->getBundles(new DelegatingParser());

        $this->assertCount(1, $bundles);
        $this->assertInstanceOf(BundleConfig::class, $bundles[0]);
        $this->assertSame(HeimrichHannotContaoUtilsBundle::class, $bundles[0]->getName());
        $this->assertSame([ContaoCoreBundle::class], $bundles[0]->getLoadAfter());
    }

    public function testRegisterContainerConfiguration()
    {
        $kernelMock = $this->createMock(Kernel::class);
        $kernelMock->method('locateResource')->willReturnCallback(function ($file, $currentDir, $first)
        {
            return $currentDir.'/../src/Resources/config/'.pathinfo($file, PATHINFO_BASENAME);
        });

        $locator = new FileLocator($kernelMock, __DIR__.'/..');

        $container = new ContainerBuilder();

        $resolver = new LoaderResolver([
            new YamlFileLoader($container, $locator),
        ]);

        $loader = new DelegatingLoader($resolver);

        $plugin = new Plugin();
        $plugin->registerContainerConfiguration($loader, []);

        $utils = [
            'huh.utils.accordion' => AccordionUtil::class,
            'huh.utils.array' => ArrayUtil::class,
            'huh.utils.container' => ContainerUtil::class,
            'huh.utils.dca' => DcaUtil::class,
            'huh.utils.image' => ImageUtil::class,
            'huh.utils.model' => ModelUtil::class,
            'huh.utils.template' => TemplateUtil::class,
        ];

        foreach ($utils as $alias => $class)
        {
            $this->assertTrue($container->has($alias));
            $definition = $container->getDefinition($alias);
            $this->assertSame($class, $definition->getClass());
            $this->assertEmpty($definition->getArguments());
            $this->assertTrue($definition->isAutowired());
        }
    }

    public function testGetExtensionConfig()
    {
        $plugin = new Plugin();
        $container = $this->createMock(\Contao\ManagerPlugin\Config\ContainerBuilder::class);
        $config = $plugin->getExtensionConfig('huh_encore', [], $container);
        $this->assertNotEmpty($config);
        $this->assertArrayHasKey('huh', $config);
        $this->assertArrayHasKey('encore', $config['huh']);

    }
}

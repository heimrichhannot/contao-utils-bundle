<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('huh_utils');

        $rootNode->children()
            ->scalarNode('tmp_folder')->defaultValue('files/tmp/huh_utils_bundle')->end()
            ->scalarNode('pdfPreviewFolder')->defaultNull()->info('Default folder where to store pdf preview images.')->end()
        ->end();

        return $treeBuilder;
    }
}

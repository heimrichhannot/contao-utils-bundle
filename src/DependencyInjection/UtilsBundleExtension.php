<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class UtilsBundleExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        /* @todo Remove this passage in version 3.0 */
        if (!isset($config['pdfPreviewFolder'])) {
            $config['pdfPreviewFolder'] = $container->getParameter('huh.utils.filecache.folder').\DIRECTORY_SEPARATOR.'pdfPreview';
        }

        $container->setParameter('huh_utils', $config);
    }
}

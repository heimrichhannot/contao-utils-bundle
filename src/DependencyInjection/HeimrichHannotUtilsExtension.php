<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class HeimrichHannotUtilsExtension extends Extension
{
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

    public function getAlias()
    {
        return 'huh_utils';
    }
}

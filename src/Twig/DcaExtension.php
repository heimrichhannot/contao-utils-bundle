<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DcaExtension extends AbstractExtension implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function getFilters()
    {
        return [
            new TwigFilter('fieldLabel', [$this, 'fieldLabel']),
        ];
    }

    public function fieldLabel(string $field, string $table): string
    {
        return $this->container->get('huh.utils.dca')->getFieldLabel($table, $field);
    }
}

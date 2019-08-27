<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class StringExtension extends AbstractExtension implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Get list of twig filters.
     *
     * @return array|\Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('autolink', [$this, 'autolink']),
        ];
    }

    public function autolink(string $text, array $options = []): string
    {
        return preg_replace_callback('@(?P<url>(?:http(s)?://)?[\w.-]+(?:\.[\w.-]+)+[\w\-._~:/?#\[\]\@!$&\'()*+,;=]+)@i', function ($matches) use ($options) {
            if (!isset($matches['url'])) {
                return '';
            }

            return '<a'.(isset($options['blank']) && $options['blank'] ? ' target=" _blank"' : '').' href="'.$matches['url'].'">'.$matches['url'].'</a>';
        }, $text, $options['limit'] ?? -1);
    }
}

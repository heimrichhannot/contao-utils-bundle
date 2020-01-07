<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Choice;

use Contao\System;
use Symfony\Component\Finder\Finder;

class TwigTemplateChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];

        $prefixes = $this->getContext();

        if (!\is_array($prefixes)) {
            $prefixes = [$prefixes];
        }

        $prefixes = array_filter($prefixes);

        $kernel = System::getContainer()->get('kernel');

        $bundles = $kernel->getBundles();
        $pattern = !empty($prefixes) ? ('/(^'.implode('|^', $prefixes).').*twig/') : '*.twig';

        if (\is_array($bundles)) {
            foreach ($bundles as $key => $value) {
                $path = $kernel->locateResource("@$key");
                $finder = new Finder();
                $finder->in($path);
                $finder->files()->name($pattern);
                $twigKey = preg_replace('/Bundle$/', '', $key);

                foreach ($finder as $val) {
                    $explodurl = explode('Resources'.\DIRECTORY_SEPARATOR.'views'.\DIRECTORY_SEPARATOR, $val->getRelativePathname());
                    $string = end($explodurl);
                    $choices[$val->getBasename('.html.twig')] = "@$twigKey/$string";
                }
            }
        }

        if (!System::getContainer()->has('huh.utils.container')) {
            return $choices;
        }

        foreach ($prefixes as $prefix) {
            $choices = array_merge($choices, System::getContainer()->get('huh.utils.template')->getTemplateGroup($prefix, 'html.twig'));
        }

        return $choices;
    }
}

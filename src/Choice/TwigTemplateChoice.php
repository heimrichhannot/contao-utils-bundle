<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
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

        if (!is_array($prefixes)) {
            $prefixes = [$prefixes];
        }

        $prefixes = array_filter($prefixes);

        $kernel = System::getContainer()->get('kernel');

        $bundles = $kernel->getBundles();

        foreach ($bundles as $key => $value) {
            $path = $kernel->locateResource("@$key");
            $finder = new Finder();
            $finder->in($path);
            $pattern = !empty($prefixes) ? ('/'.implode('|', $prefixes).'.*twig/') : '*.twig';
            $finder->files()->name($pattern);
            foreach ($finder as $val) {
                $explodurl = explode('Resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR, $val->getRelativePathname());
                $string = end($explodurl);
                $string = str_replace('\\', ':', $string);
                $choices[] = "@$key:$string";
            }
        }

        return $choices;
    }
}

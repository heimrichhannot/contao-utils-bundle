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
            $array = explode(DIRECTORY_SEPARATOR, $path);
            if (in_array('vendor', $array, true)) {
                continue;
            }
            $finder = new Finder();
            $finder->in($path);
            $finder->files()->name('*.twig');
            foreach ($finder as $val) {
                if (!empty($prefixes) && empty(System::getContainer()->get('huh.utils.array')->filterByPrefixes([$val->getFilename() => $val->getFilename()], $prefixes))) {
                    continue;
                }

                $explodurl = explode('Resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR, $val->getRelativePathname());
                $string = end($explodurl);
                $string = str_replace('\\', ':', $string);
                $choices[] = "@$key:$string";
            }
        }

        return $choices;
    }
}

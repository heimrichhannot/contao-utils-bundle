<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Twig;

use HeimrichHannot\UtilsBundle\Util\Utils;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class StringExtension extends AbstractExtension
{
    public function __construct(
        protected Utils $utils,
    )
    {
    }

    /**
     * Get list of twig filters.
     *
     * @return array|\Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('anonymize_email', [$this, 'anonymizeEmail']),
        ];
    }

    public function anonymizeEmail(string $text): string
    {
        return $this->utils->anonymize()->anonymizeEmail($text);
    }
}

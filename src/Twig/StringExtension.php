<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Twig;

use HeimrichHannot\UtilsBundle\String\AnonymizerUtil;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class StringExtension extends AbstractExtension
{
    /**
     * @var AnonymizerUtil
     */
    private $anonymizerUtil;

    public function __construct(AnonymizerUtil $anonymizerUtil)
    {
        $this->anonymizerUtil = $anonymizerUtil;
    }

    /**
     * Get list of twig filters.
     *
     * @return array|\Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('autolink', [$this, 'autolink']),
            new TwigFilter('anonymize_email', [$this, 'anonymizeEmail']),
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
    
    public function anonymizeEmail(string $text): string
    {
        return $this->anonymizerUtil->anonymizeEmail($text);
    }
}

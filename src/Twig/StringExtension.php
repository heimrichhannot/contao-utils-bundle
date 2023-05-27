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
            new TwigFilter('autolink', [$this, 'autolink']),
            new TwigFilter('anonymize_email', [$this, 'anonymizeEmail']),
        ];
    }

    /**
     * Automatically link urls with given text.
     *
     * Options:
     * - blank (boolean): add target="_blank" attribute
     * - limit (int): The maximum possible replacements in each subject string. Defaults to -1 (no limit).
     */
    public function autolink(string $text, array $options = []): string
    {
        $options = array_merge([
            'blank' => false,
            'limit' => -1,
        ], $options);

        $replacement = function ($matches) use ($options) {
            if (!isset($matches['url'])) {
                return '';
            }

            return sprintf(
                '<a%s href="%s">%s</a>',
                ($options['blank'] ? ' target=" _blank"' : ''),
                $matches['url'],
                $matches['url'],
            );
        };

        $pattern = '@(?P<url>(?:http(s)?://)[\w.-]+(?:\.[\w.-]+)+[\w\-._~:/?#\[\]\@!$&\'()*+,;=]+)@i';
        return preg_replace_callback($pattern, $replacement, $text, $options['limit']);
    }

    public function anonymizeEmail(string $text): string
    {
        return $this->utils->anonymize()->anonymizeEmail($text);
    }
}

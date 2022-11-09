<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util\Html;

class HtmlUtil
{
    /**
     * Generate a attribute string for html elements out of an array.
     *
     * Options:
     * - xhtml: (bool) XHTML format instead of HTML5 format. Default false
     */
    public function generateAttributeString(array $attributes, array $options = []): string
    {
        $options = array_merge([
            'xhtml' => false,
        ], $options);

        return trim(implode(' ', array_map(function ($key) use ($attributes, $options) {
            if (\is_bool($attributes[$key])) {
                if ($options['xhtml']) {
                    return $attributes[$key] ? sprintf('%s="%s"', $key, $key) : '';
                }

                return $attributes[$key] ? $key : '';
            }

            return $key.'="'.$attributes[$key].'"';
        }, array_keys($attributes))));
    }
}

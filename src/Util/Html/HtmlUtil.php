<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util\Html;

use function Symfony\Component\String\u;

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

    /**
     * Generates a data-attributes string out of an array.
     *
     * Options (additional to Options from HtmlUtl::generateAttributeString()):
     * - normalizeKeys: Array keys are normalized to lowercase dash-cased strings (e.g. Foo Bar_player is transformed to foo-bar-player)
     */
    public function generateDataAttributesString(array $attributes, array $options = []): string
    {
        $options = array_merge([
            'xhtml' => false,
            'normalizeKeys' => true,
            'array_handling' => 'reduce',
        ], $options);

        if (!\in_array($options['array_handling'], ['reduce', 'encode'])) {
            $options['array_handling'] = 'reduce';
        }

        $dataAttributes = [];

        foreach ($attributes as $key => $value) {
            if (false === $value) {
                continue;
            }

            if (\is_array($value)) {
                if ('reduce' === $options['array_handling']) {
                    $value = implode(' ', array_reduce($value, function ($tokens, $token) {
                        if (\is_string($token)) {
                            $token = trim($token);

                            if (\strlen($token) > 0) {
                                $tokens[] = $token;
                            }
                        } elseif (is_numeric($token)) {
                            $tokens[] = $token;
                        }

                        return $tokens;
                    }, []));

                    if (empty($value)) {
                        continue;
                    }
                } elseif ('encode' === $options['array_handling']) {
                    $value = htmlspecialchars(json_encode($value), \ENT_QUOTES, 'UTF-8');
                }
            }

            if ($options['normalizeKeys']) {
                $key = str_replace('_', '-', u($key)->snake());
            }

            if (!str_starts_with($key, 'data-')) {
                $key = 'data-'.$key;
            }

            $dataAttributes[$key] = $value;
        }

        unset($options['normalizeKeys']);

        return $this->generateAttributeString($dataAttributes, $options);
    }
}

<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\String;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Html2Text\Html2Text;

class StringUtil
{
    const CAPITAL_LETTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const CAPITAL_LETTERS_NONAMBIGUOUS = 'ABCDEFGHJKLMNPQRSTUVWX';
    const SMALL_LETTERS = 'abcdefghijklmnopqrstuvwxyz';
    const SMALL_LETTERS_NONAMBIGUOUS = 'abcdefghjkmnpqrstuvwx';
    const NUMBERS = '0123456789';
    const NUMBERS_NONAMBIGUOUS = '23456789';

    /** @var ContaoFrameworkInterface */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Check for the occurrence at the start of the string.
     *
     * @param $haystack string The string to search in
     * @param $needle   string The needle
     *
     * @return bool
     */
    public function startsWith($haystack, $needle)
    {
        return '' === $needle || false !== strrpos($haystack, $needle, -\strlen($haystack));
    }

    /**
     * Check for the occurrence at the end of the string.
     *
     * @param string $haystack The string to search in
     * @param string $needle   The needle
     *
     * @return bool
     */
    public function endsWith($haystack, $needle)
    {
        // search forward starting from end minus needle length characters
        return '' === $needle || (($temp = \strlen($haystack) - \strlen($needle)) >= 0 && false !== strpos($haystack, $needle, $temp));
    }

    public function camelCaseToDashed($value)
    {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $value));
    }

    /**
     * @codeCoverageIgnore
     *
     * @param bool $includeAmbiguousChars
     *
     * @return mixed
     */
    public function randomChar(bool $includeAmbiguousChars = false)
    {
        if ($includeAmbiguousChars) {
            $chars = static::CAPITAL_LETTERS.static::SMALL_LETTERS.static::NUMBERS;
        } else {
            $chars = static::CAPITAL_LETTERS_NONAMBIGUOUS.static::SMALL_LETTERS_NONAMBIGUOUS.static::NUMBERS_NONAMBIGUOUS;
        }

        return $chars[rand(0, $includeAmbiguousChars ? 61 : 50)];
    }

    /**
     * @codeCoverageIgnore
     *
     * @param bool $includeAmbiguousChars
     *
     * @return mixed
     */
    public function randomLetter(bool $includeAmbiguousChars = false)
    {
        if ($includeAmbiguousChars) {
            $chars = static::CAPITAL_LETTERS.static::SMALL_LETTERS;
        } else {
            $chars = static::CAPITAL_LETTERS_NONAMBIGUOUS.static::SMALL_LETTERS_NONAMBIGUOUS;
        }

        return $chars[rand(0, $includeAmbiguousChars ? 51 : 42)];
    }

    /**
     * @codeCoverageIgnore
     *
     * @param bool $includeAmbiguousChars
     *
     * @return mixed
     */
    public function randomNumber(bool $includeAmbiguousChars = false)
    {
        if ($includeAmbiguousChars) {
            $chars = static::NUMBERS;
        } else {
            $chars = static::NUMBERS_NONAMBIGUOUS;
        }

        return $chars[rand(0, $includeAmbiguousChars ? 9 : 7)];
    }

    /**
     * @codeCoverageIgnore
     *
     * @param string $charList
     *
     * @return mixed
     */
    public function random(string $charList)
    {
        return $charList[rand(0, \strlen($charList) - 1)];
    }

    /**
     * Truncates a given string respecting html element.
     *
     * @param string $text
     * @param int    $length
     * @param string $ending
     * @param bool   $exact
     * @param bool   $considerHtml
     *
     * @return bool|string
     */
    public function truncateHtml(string $text, int $length = 100, string $ending = '&nbsp;&hellip;', bool $exact = false, bool $considerHtml = true)
    {
        $open_tags = [];

        if ($considerHtml) {
            // if the plain text is shorter than the maximum length, return the whole text
            if (\strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }
            // splits all html-tags to scanable lines
            preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
            $total_length = \strlen($ending);
            $truncate = '';
            foreach ($lines as $line_matchings) {
                // if there is any html-tag in this line, handle it and add it (uncounted) to the output
                if (!empty($line_matchings[1])) {
                    // if it's an "empty element" with or without xhtml-conform closing slash
                    if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
                        // do nothing
                        // if tag is a closing tag
                    } else {
                        if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
                            // delete tag from $open_tags list
                            $pos = array_search($tag_matchings[1], $open_tags, true);
                            if (false !== $pos) {
                                unset($open_tags[$pos]);
                            }
                            // if tag is an opening tag
                        } else {
                            if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
                                // add tag to the beginning of $open_tags list
                                array_unshift($open_tags, strtolower($tag_matchings[1]));
                            }
                        }
                    }
                    // add html-tag to $truncate'd text
                    $truncate .= $line_matchings[1];
                }
                // calculate the length of the plain text part of the line; handle entities as one character
                $content_length = \strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
                if ($total_length + $content_length > $length) {
                    // the number of characters which are left
                    $left = $length - $total_length;
                    $entities_length = 0;
                    // search for html entities
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                        // calculate the real length of all entities in the legal range
                        foreach ($entities[0] as $entity) {
                            if ($entity[1] + 1 - $entities_length <= $left) {
                                --$left;
                                $entities_length += \strlen($entity[0]);
                            } else {
                                // no more characters left
                                break;
                            }
                        }
                    }
                    $truncate .= substr($line_matchings[2], 0, $left + $entities_length);
                    // maximum lenght is reached, so get off the loop
                    break;
                }
                $truncate .= $line_matchings[2];
                $total_length += $content_length;

                // if the maximum length is reached, get off the loop
                if ($total_length >= $length) {
                    break;
                }
            }
        } else {
            if (\strlen($text) <= $length) {
                return $text;
            }
            $truncate = substr($text, 0, $length - \strlen($ending));
        }
        // if the words shouldn't be cut in the middle...
        if (!$exact) {
            // ...search the last occurance of a space...
            $spacepos = strrpos($truncate, ' ');
            if (isset($spacepos)) {
                // ...and cut the text in this position
                $truncate = substr($truncate, 0, $spacepos);
            }
        }
        // add the defined ending to the text
        $truncate .= $ending;
        if ($considerHtml) {
            // close all unclosed html-tags
            foreach ($open_tags as $tag) {
                $truncate .= '</'.$tag.'>';
            }
        }

        return $truncate;
    }

    /**
     * @param string $regExp
     * @param string $subject
     *
     * @return mixed|string
     */
    public function pregReplaceLast(string $regExp, string $subject)
    {
        if (!$regExp) {
            return $subject;
        }

        $strDelimiter = $regExp[0];
        $regExp = rtrim(ltrim($regExp, $strDelimiter), $strDelimiter);

        return preg_replace("$strDelimiter$regExp(?!.*$regExp)$strDelimiter", '', $subject);
    }

    public function removeLeadingAndTrailingSlash(string $string): string
    {
        return rtrim(ltrim($string, '/'), '/');
    }

    public function removeLeadingString(string $string, string $subject)
    {
        return preg_replace('@^'.$string.'@i', '', $subject);
    }

    public function removeTrailingString(string $string, string $subject)
    {
        return preg_replace('@'.$string.'$@i', '', $subject);
    }

    /**
     * Restore basic entities.
     *
     * @param string $string The string with the tags to be replaced
     *
     * @return string The string with the original entities
     */
    public function restoreBasicEntities($string)
    {
        return str_replace(['[&]', '[&amp;]', '[lt]', '[gt]', '[nbsp]', '[-]'], ['&amp;', '&amp;', '&lt;', '&gt;', '&nbsp;', '&shy;'], $string);
    }

    /**
     * @param       $text
     * @param array $cssText the css as text (no paths allowed atm)
     *
     * @throws \TijsVerkoyen\CssToInlineStyles\Exception
     */
    public function convertToInlineCss(string $text, string $cssText)
    {
        // prevent inlining inside conditional comments, see https://github.com/tijsverkoyen/CssToInlineStyles/issues/133
        $cssText = preg_replace('/<!--(.*?)-->/Uis', '', $cssText);

        // apply the css inliner
        $objCssInliner = new \TijsVerkoyen\CssToInlineStyles\CssToInlineStyles($text, $cssText);

        return $objCssInliner->convert();
    }

    /**
     * @param string $html
     * @param array  $options
     *
     * @return string
     */
    public function html2Text(string $html, array $options = [])
    {
        $html = str_replace("\n", '', $html); // remove white spaces from html
        $html = str_replace('</p>', '<br /></p>', $html); // Html2Text will replace paragraph by only one break
        $objConverter = new Html2Text($html, $options);

        return $objConverter->getText();
    }

    /**
     * Convenience method for lower casing in a save callback.
     *
     * @param                $value
     * @param \DataContainer $objDc
     */
    public function lowerCase($value, \DataContainer $objDc)
    {
        return trim(strtolower($value));
    }
}

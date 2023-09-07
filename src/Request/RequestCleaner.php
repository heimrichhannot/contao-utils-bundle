<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Request;

use Contao\Input;
use Contao\StringUtil;
use Contao\Validator;
use Symfony\Component\CssSelector\Exception\SyntaxErrorException;
use Wa72\HtmlPageDom\HtmlPageCrawler;

/**
 * @internal
 */
class RequestCleaner
{
    /**
     * XSS clean, decodeEntities, tidy/strip tags, encode special characters and encode inserttags and return save, cleaned value(s).
     *
     * @param mixed $value            The input value
     * @param bool  $decodeEntities   If true, all entities will be decoded
     * @param bool  $encodeInsertTags If true, encode the opening and closing delimiters of insert tags
     * @param bool  $tidy             If true, varValue is tidied up
     * @param bool  $strictMode       If true, the xss cleaner removes also JavaScript event handlers
     *
     * @return mixed The cleaned value
     */
    public function clean($value, bool $decodeEntities = false, bool $encodeInsertTags = true, bool $tidy = true, bool $strictMode = true)
    {
        // do not clean, otherwise empty string will be returned, not null
        if (null === $value) {
            return $value;
        }

        if (\is_array($value)) {
            foreach ($value as $i => $childValue) {
                $value[$i] = $this->clean($childValue, $decodeEntities, $encodeInsertTags, $tidy, $strictMode);
            }

            return $value;
        }

        // do not handle binary uuid
        if (Validator::isUuid($value)) {
            return $value;
        }

        $value = $this->xssClean($value, $strictMode);

        if ($tidy) {
            $value = $this->tidy($value);
        } else {
            // decodeEntities for tidy is more complex, because non allowed tags should be displayed as readable text, not as html entity
            $value = Input::decodeEntities($value);
        }

        // do not encodeSpecialChars when tidy did run, otherwise non allowed tags will be encoded twice
        if (!$decodeEntities && !$tidy) {
            $value = Input::encodeSpecialChars($value);
        }

        if ($encodeInsertTags) {
            $value = Input::encodeInsertTags($value);
        }

        return $value;
    }

    /**
     * XSS clean, decodeEntities, tidy/strip tags, encode special characters and encode inserttags and return save, cleaned value(s).
     *
     * @param mixed  $value            The input value
     * @param bool   $decodeEntities   If true, all entities will be decoded
     * @param bool   $encodeInsertTags If true, encode the opening and closing delimiters of insert tags
     * @param string $allowedTags      List of allowed html tags
     * @param bool   $tidy             If true, varValue is tidied up
     * @param bool   $strictMode       If true, the xss cleaner removes also JavaScript event handlers
     *
     * @return mixed The cleaned value
     */
    public function cleanHtml($value, bool $decodeEntities = false, bool $encodeInsertTags = true, string $allowedTags = '', bool $tidy = true, bool $strictMode = true)
    {
        // do not clean, otherwise empty string will be returned, not null
        if (null === $value) {
            return $value;
        }

        if (\is_array($value)) {
            foreach ($value as $i => $childValue) {
                $value[$i] = $this->cleanHtml($childValue, $decodeEntities, $encodeInsertTags, $allowedTags, $tidy, $strictMode);
            }

            return $value;
        }

        // do not handle binary uuid
        if (Validator::isUuid($value)) {
            return $value;
        }

        $value = $this->xssClean($value, $strictMode);

        if ($tidy) {
            $value = $this->tidy($value, $allowedTags, $decodeEntities);
        } else {
            // decodeEntities for tidy is more complex, because non allowed tags should be displayed as readable text, not as html entity
            $value = Input::decodeEntities($value);
        }

        // do not encodeSpecialChars when tidy did run, otherwise non allowed tags will be encoded twice
        if (!$decodeEntities && !$tidy) {
            $value = Input::encodeSpecialChars($value);
        }

        if ($encodeInsertTags) {
            $value = Input::encodeInsertTags($value);
        }

        return $value;
    }

    /**
     * Clean a value and try to prevent XSS attacks.
     *
     * @param mixed $varValue   A string or array
     * @param bool  $strictMode If true, the function removes also JavaScript event handlers
     *
     * @return mixed The cleaned string or array
     */
    public function xssClean($varValue, bool $strictMode = false)
    {
        if (\is_array($varValue)) {
            foreach ($varValue as $key => $value) {
                $varValue[$key] = $this->xssClean($value, $strictMode);
            }

            return $varValue;
        }

        // do not xss clean binary uuids
        if (Validator::isBinaryUuid($varValue)) {
            return $varValue;
        }

        // Fix issue StringUtils::decodeEntites() returning empty string when value is 0 in some contao 4.9 versions
        if ('0' !== $varValue && 0 !== $varValue) {
            $varValue = StringUtil::decodeEntities($varValue);
        }

        $varValue = preg_replace('/(&#[A-Za-z0-9]+);?/i', '$1;', $varValue);

        // fix: "><script>alert('xss')</script> or '></SCRIPT>">'><SCRIPT>alert(String.fromCharCode(88,83,83))</SCRIPT>
        $varValue = preg_replace('/(?<!\w)(?>["|\']>)+(<[^\/^>]+>.*)/', '$1', $varValue);

        $varValue = Input::xssClean($varValue, $strictMode);

        return $varValue;
    }

    /**
     * Tidy an value.
     *
     * @param string $varValue       Input value
     * @param string $allowedTags    Allowed tags as string `<p><span>`
     * @param bool   $decodeEntities If true, all entities will be decoded
     *
     * @return string The tidied string
     */
    public function tidy($varValue, string $allowedTags = '', bool $decodeEntities = false): string
    {
        if (!$varValue) {
            return $varValue;
        }

        // do not tidy non-xss critical characters for performance
        if (!preg_match('#"|\'|<|>|\(|\)#', StringUtil::decodeEntities($varValue))) {
            return $varValue;
        }

        // remove illegal white spaces after closing tag slash <br / >
        $varValue = preg_replace('@\/(\s+)>@', '/>', $varValue);

        // Encode opening tag arrow brackets
        $varValue = preg_replace_callback('/<(?(?=!--)!--[\s\S]*--|(?(?=\?)\?[\s\S]*\?|(?(?=\/)\/[^.\-\d][^\/\]\'"[!#$%&()*+,;<=>?@^`{|}~ ]*|[^.\-\d][^\/\]\'"[!#$%&()*+,;<=>?@^`{|}~ ]*(?:\s[^.\-\d][^\/\]\'"[!#$%&()*+,;<=>?@^`{|}~ ]*(?:=(?:"[^"]*"|\'[^\']*\'|[^\'"<\s]*))?)*)\s?\/?))>/', function ($matches) {
            return substr_replace($matches[0], '&lt;', 0, 1);
        }, $varValue);

        // Encode less than signs that are no tags with [lt]
        $varValue = str_replace('<', '[lt]', $varValue);

        // After we saved less than signs with [lt] revert &lt; sign to <
        $varValue = StringUtil::decodeEntities($varValue);

        // Restore HTML comments
        $varValue = str_replace(['&lt;!--', '&lt;!['], ['<!--', '<!['], $varValue);

        // Recheck for encoded null bytes
        while (false !== strpos($varValue, '\\0')) {
            $varValue = str_replace('\\0', '', $varValue);
        }

        $objCrawler = new HtmlPageCrawler($varValue);

        if (!$objCrawler->isHtmlDocument()) {
            $objCrawler = new HtmlPageCrawler('<div id="tidyWrapperx123x123xawec3">'.$varValue.'</div>');
        }

        $arrAllowedTags = explode('<', str_replace('>', '', $allowedTags));
        $arrAllowedTags = array_filter($arrAllowedTags);

        try {
            if (!empty($arrAllowedTags)) {
                $objCrawler->filter('*')->each(function ($node, $i) use ($arrAllowedTags) {
                    /** @var $node HtmlPageCrawler */

                    // skip wrapper
                    if ('tidyWrapperx123x123xawec3' === $node->getAttribute('id')) {
                        return $node;
                    }

                    if (!\in_array($node->getNode(0)->tagName, $arrAllowedTags, true)) {
                        $strHTML = $node->saveHTML();
                        $strHTML = str_replace(['<', '>'], ['[[xlt]]', '[[xgt]]'], $strHTML);

                        // remove unwanted tags and return the element text
                        return $node->replaceWith($strHTML);
                    }

                    return $node;
                });
            }
            // unwrap div#tidyWrapper and set value to its innerHTML
            if (!$objCrawler->isHtmlDocument()) {
                $varValue = $objCrawler->filter('div#tidyWrapperx123x123xawec3')->getInnerHtml();
            } else {
                $varValue = $objCrawler->saveHTML();
            }

            // HTML documents or fragments, Crawler first converts all non-ASCII characters to entities (see: https://github.com/wasinger/htmlpagedom/issues/5)
            $varValue = StringUtil::decodeEntities($varValue);

            // trim last [nbsp] occurance
            $varValue = preg_replace('@(\[nbsp\])+@', '', $varValue);
        } catch (SyntaxErrorException $e) {
        }

        $varValue = $this->restoreBasicEntities($varValue, $decodeEntities);

        if (!$decodeEntities) {
            $varValue = Input::encodeSpecialChars($varValue);
        }

        // encode unwanted tag opening and closing brakets
        $arrSearch = ['[[xlt]]', '[[xgt]]'];
        $arrReplace = ['&#60;', '&#62;'];
        $varValue = str_replace($arrSearch, $arrReplace, $varValue);

        return $varValue;
    }

    /**
     * Restore basic entities.
     *
     * @param string $buffer         The string with the tags to be replaced
     * @param bool   $decodeEntities If true, all entities will be decoded
     *
     * @return string The string with the original entities
     */
    public function restoreBasicEntities(string $buffer, bool $decodeEntities = false): string
    {
        $buffer = str_replace(['[&]', '[&amp;]', '[lt]', '[gt]', '[nbsp]', '[-]'], ['&amp;', '&amp;', '&lt;', '&gt;', '&nbsp;', '&shy;'], $buffer);

        if ($decodeEntities) {
            $buffer = StringUtil::decodeEntities($buffer);
        }

        return $buffer;
    }
}

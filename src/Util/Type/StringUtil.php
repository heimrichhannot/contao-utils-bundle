<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util\Type;

use DOMDocument;
use DOMNode;
use DOMText;
use HeimrichHannot\UtilsBundle\Dom\DOMLettersIterator;

class StringUtil
{
    private const CAPITAL_LETTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const CAPITAL_LETTERS_NONAMBIGUOUS = 'ABCDEFGHJKLMNPQRSTUVWX';
    private const SMALL_LETTERS = 'abcdefghijklmnopqrstuvwxyz';
    private const SMALL_LETTERS_NONAMBIGUOUS = 'abcdefghjkmnpqrstuvwx';
    private const NUMBERS = '0123456789';
    private const NUMBERS_NONAMBIGUOUS = '23456789';

    /**
     * Convert a camel case string to a dashed string.
     *
     * Example: MyPrettyClass to my-pretty-class
     *
     * @param $value
     */
    public function camelCaseToDashed(string $value): string
    {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $value));
    }

    /**
     * Convert a camel case string to a snake cased string.
     *
     * Example: MyPrettyClass to my_pretty_class
     */
    public function camelCaseToSnake(string $value): string
    {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1_', $value));
    }

    /**
     * Return a random char. Can be a letter or a number.
     *
     * @param array $options Pass additional options. See self::random()
     */
    public function randomChar(bool $includeAmbiguousChars = false, array $options = []): string
    {
        if ($includeAmbiguousChars) {
            $chars = self::CAPITAL_LETTERS.self::SMALL_LETTERS.self::NUMBERS;
        } else {
            $chars = self::CAPITAL_LETTERS_NONAMBIGUOUS.self::SMALL_LETTERS_NONAMBIGUOUS.self::NUMBERS_NONAMBIGUOUS;
        }

        return $this->random($chars, $options);
    }

    /**
     * Return a random letter char.
     *
     * @param array $options Pass additional options. See self::random()
     */
    public function randomLetter(bool $includeAmbiguousChars = false, array $options = []): string
    {
        if ($includeAmbiguousChars) {
            $chars = self::CAPITAL_LETTERS.self::SMALL_LETTERS;
        } else {
            $chars = self::CAPITAL_LETTERS_NONAMBIGUOUS.self::SMALL_LETTERS_NONAMBIGUOUS;
        }

        return $this->random($chars, $options);
    }

    /**
     * Return a random number char.
     *
     * @param array $options Pass additional options. See self::random()
     */
    public function randomNumber(bool $includeAmbiguousChars = false, array $options = []): string
    {
        if ($includeAmbiguousChars) {
            $chars = self::NUMBERS;
        } else {
            $chars = self::NUMBERS_NONAMBIGUOUS;
        }

        return $this->random($chars, $options);
    }

    /**
     * Return a random char of a given string.
     *
     * Options:
     * - randomNumberGenerator: (callable) A custom callback function to generate a random number. Get min and max as parameter.
     *
     * @throws \UnexpectedValueException if the return value of the random number generator is higher that string length (\strlen($charList) - 1)
     */
    public function random(string $charList, array $options = []): string
    {
        if ('' === $charList) {
            return $charList;
        }

        if (isset($options['randomNumberGenerator']) && \is_callable($options['randomNumberGenerator'])) {
            $number = $options['randomNumberGenerator'](0, \strlen($charList) - 1);
        } else {
            $number = rand(0, \strlen($charList) - 1);
        }

        if ($number > (\strlen($charList) - 1)) {
            throw new \UnexpectedValueException('The random number is out of range!');
        }

        return $charList[$number];
    }

    /**
     * Truncates the text of a html string. By default, the last word is kept complete.
     *
     * Credits: https://www.pjgalbraith.com/truncating-text-html-with-php/
     *
     * Additional options:
     * - exact: (bool) Cut text exact on character limit instead after the word
     *
     * @param string $html     The html string that should be truncated
     * @param int    $limit    Max number of text characters (html tags are not counted)
     * @param string $ellipsis Characters that should be displayed, where the string is truncated
     * @param array  $options  Additional Options
     */
    public function truncateHtml(string $html, int $limit, string $ellipsis = '…', array $options = []): string
    {
        $defaults = [
            'exact' => false,
        ];
        $options = array_merge($defaults, $options);

        if ($limit <= 0 || $limit >= \strlen(strip_tags($html))) {
            return $html;
        }

        $dom = new DOMDocument();

        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

        $body = $dom->getElementsByTagName('body')->item(0);

        $lettersIterator = new DOMLettersIterator($body);

        foreach ($lettersIterator as $letter) {
            if ($lettersIterator->key() >= $limit) {
                [$currentNode, $offset, $previousNode] = $lettersIterator->currentTextPosition();

                if (true === $options['exact']) {
                    $currentNode->nodeValue = substr($currentNode->nodeValue, 0, $offset + 1);
                } elseif (\strlen($currentNode->nodeValue) > ($offset + 1)) {
                    $truncatedText = substr($currentNode->nodeValue, 0, $offset + 1);
                    $wordStopPosition = strripos($truncatedText, ' ');

                    if (false !== $wordStopPosition) {
                        $currentNode->nodeValue = substr(
                            $truncatedText, 0,
                            $wordStopPosition
                        );
                    } else {
                        if (!$currentNode->isSameNode($previousNode)) {
                            $currentNode = $previousNode;
                        } else {
                            $currentNode->nodeValue = '';
                        }
                    }
                }
                $this->removeProceedingNodes($currentNode, $body);
                $this->insertEllipsis($currentNode, $ellipsis);

                break;
            }
        }

        return preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', html_entity_decode($dom->saveHTML()));
    }

    /**
     * Replace the last match of string with preg_replace.
     */
    public function pregReplaceLast(string $regExp, string $subject, string $replacement = ''): ?string
    {
        if (!$regExp) {
            return $subject;
        }

        $delimiter = $regExp[0];
        $regExp = rtrim(ltrim($regExp, $delimiter), $delimiter);

        return preg_replace("$delimiter$regExp(?!.*$regExp)$delimiter", $replacement, $subject);
    }

    /**
     * Convert an xml string to array.
     */
    public function convertXmlToArray(string $xmlData): ?array
    {
        $xmlObject = simplexml_load_string($xmlData, 'SimpleXMLElement', \LIBXML_NOCDATA);

        return json_decode(json_encode($xmlObject), true);
    }

    /**
     * Remove a string from the beginning of $subject.
     *
     * Options:
     * - trim: (bool) Trim whitespace from the beginning after a leading string is removed. Default true.
     */
    public function removeLeadingString(string $string, string $subject, array $options = []): ?string
    {
        $options = array_merge([
            'trim' => true,
        ], $options);
        $result = preg_replace('@^'.$string.'@i', '', $subject);

        if (true === $options['trim']) {
            $result = ltrim($result);
        }

        return $result;
    }

    /**
     * Remove a string from the end of $subject.
     *
     * Options:
     * - trim: (bool) Trim whitespace from the end after a trailing string is removed. Default true.
     */
    public function removeTrailingString(string $string, string $subject, array $options = []): ?string
    {
        $options = array_merge([
            'trim' => true,
        ], $options);
        $result = preg_replace('@'.$string.'$@i', '', $subject);

        if (true === $options['trim']) {
            $result = rtrim($result);
        }

        return $result;
    }

    /**
     * Add an ellipsis to the end of html text.
     *
     * Used in self::truncateHtml()
     *
     * @param $ellipsis
     *
     * @internal
     */
    private function insertEllipsis(DOMNode $domNode, $ellipsis)
    {
        $avoid = ['a', 'strong', 'em', 'h1', 'h2', 'h3', 'h4', 'h5']; //html tags to avoid appending the ellipsis to

        if (\in_array($domNode->parentNode->nodeName, $avoid) && null !== $domNode->parentNode->parentNode) {
            // Append as text node to parent instead
            $textNode = new DOMText($ellipsis);

            if ($domNode->parentNode->parentNode->nextSibling) {
                // currently not testable, as there should be never a sibling to parent parent node in truncateHtml result here
                // @codeCoverageIgnoreStart
                $domNode->parentNode->parentNode->insertBefore($textNode, $domNode->parentNode->parentNode->nextSibling);
            // @codeCoverageIgnoreEnd
            } else {
                $domNode->parentNode->parentNode->appendChild($textNode);
            }
        } else {
            // Append to current node
            $domNode->nodeValue = rtrim($domNode->nodeValue).$ellipsis;
        }
    }

    /**
     * Remove proceeding nodes from dom.
     *
     * Used in self::truncateHtml()
     *
     * @internal
     */
    private function removeProceedingNodes(DOMNode $currentNode, DOMNode $rootNode)
    {
        $nextNode = $currentNode->nextSibling;

        if (null !== $nextNode) {
            $this->removeProceedingNodes($nextNode, $rootNode);
            $currentNode->parentNode->removeChild($nextNode);
        } else {
            //scan upwards till we find a sibling
            $curNode = $currentNode->parentNode;

            while ($curNode !== $rootNode) {
                if (null !== $curNode->nextSibling) {
                    $curNode = $curNode->nextSibling;
                    $this->removeProceedingNodes($curNode, $rootNode);
                    $curNode->parentNode->removeChild($curNode);

                    break;
                }
                $curNode = $curNode->parentNode;
            }
        }
    }
}

<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Dom;

use DOMDocument;
use DOMElement;
use DOMNode;
use InvalidArgumentException;
use Iterator;

/**
 * Iterates individual characters (Unicode codepoints) of DOM text and CDATA nodes
 * while keeping track of their position in the document.
 *
 * Example:
 *
 *  $doc = new DOMDocument();
 *  $doc->load('example.xml');
 *  foreach(new DOMLettersIterator($doc) as $letter) echo $letter;
 *
 * NB: If you only need characters without their position
 *     in the document, use DOMNode->textContent instead.
 *
 * @author porneL http://pornel.net
 * @license Public Domain
 */
final class DOMLettersIterator implements Iterator
{
    private $start;
    private $current;
    private $offset;
    private $key;
    private $letters;
    /**
     * @var \SplQueue
     */
    private $queue;

    /**
     * expects DOMElement or DOMDocument (see DOMDocument::load and DOMDocument::loadHTML).
     *
     * @param DOMDocument|DOMElement $el
     */
    public function __construct(DOMNode $el)
    {
        if ($el instanceof DOMDocument) {
            $this->start = $el->documentElement;
        } else {
            if ($el instanceof DOMElement) {
                $this->start = $el;
            } else {
                throw new InvalidArgumentException('Invalid arguments, expected DOMElement or DOMDocument');
            }
        }
        $this->queue = new \SplQueue();
    }

    /**
     * Returns position in text as DOMText node and character offset.
     * (it's NOT a byte offset, you must use mb_substr() or similar to use this offset properly).
     * node may be NULL if iterator has finished.
     *
     * @return DOMElement[]|int[]
     */
    public function currentTextPosition()
    {
        return [$this->current, $this->offset, $this->queue->bottom()];
    }

    /**
     * Returns DOMElement that is currently being iterated or NULL if iterator has finished.
     *
     * @return DOMElement
     */
    public function currentElement()
    {
        return $this->current ? $this->current->parentNode : null;
    }

    // Implementation of Iterator interface
    public function key()
    {
        return $this->key;
    }

    public function next(): void
    {
        if (!$this->current) {
            return;
        }

        if ($this->isTextNode($this->current)) {
            if (-1 == $this->offset) {
                // fastest way to get individual Unicode chars and does not require mb_* functions
                preg_match_all('/./us', $this->current->textContent, $m);
                $this->letters = $m[0];
            }
            ++$this->offset;
            ++$this->key;

            if ($this->offset < \count($this->letters)) {
                return;
            }
            $this->offset = -1;
            --$this->key;
        }

        $previousTextElement = $this->current;

        while (XML_ELEMENT_NODE == $this->current->nodeType && $this->current->firstChild) {
            $this->current = $this->current->firstChild;

            if ($this->isTextNode($this->current)) {
                $this->addToQueue($this->current);
                $this->next();

                return;
            }
        }

        while (!$this->current->nextSibling && $this->current->parentNode) {
            $this->current = $this->current->parentNode;

            if ($this->current === $this->start) {
                $this->current = null;

                return;
            }
        }

        $this->current = $this->current->nextSibling;

        if ($this->isTextNode($this->current)) {
            $this->addToQueue($this->current);
        }

        $this->next();
    }

    public function current()
    {
        if ($this->current) {
            return $this->letters[$this->offset];
        }

        return null;
    }

    public function valid()
    {
        return (bool) $this->current;
    }

    public function rewind()
    {
        $this->offset = -1;
        $this->letters = [];
        $this->current = $this->start;
        $this->next();
    }

    private function isTextNode(DOMNode $element): bool
    {
        return XML_TEXT_NODE == $element->nodeType || XML_CDATA_SECTION_NODE == $element->nodeType;
    }

    private function addToQueue(DOMNode $node): void
    {
        $this->queue->enqueue($node);

        while ($this->queue->count() > 2) {
            $this->queue->dequeue();
        }
    }
}

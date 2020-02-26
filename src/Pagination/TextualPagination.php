<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Pagination;

use Contao\FrontendTemplate;
use Contao\Pagination;
use Contao\StringUtil;
use Contao\Template;

class TextualPagination extends Pagination
{
    /**
     * @var array
     */
    protected $teasers = [];

    /**
     * @var string
     */
    protected $singlePageUrl;

    /**
     * Set the number of rows, the number of results per pages and the number of links.
     *
     * @param array    $teasers          The teasers for the pagination
     * @param int      $intRows          The number of rows
     * @param int      $intPerPage       The number of items per page
     * @param int      $intNumberOfLinks The number of links to generate
     * @param string   $strParameter     The parameter name
     * @param Template $objTemplate      The template object
     * @param bool     $blnForceParam    Force the URL parameter
     */
    public function __construct(array $teasers, string $singlePageUrl, $intRows, $intPerPage, $intNumberOfLinks = 7, $strParameter = 'page', Template $objTemplate = null, $blnForceParam = false)
    {
        parent::__construct($intRows, $intPerPage, $intNumberOfLinks, $strParameter, $objTemplate, $blnForceParam);

        $this->teasers = $teasers;
        $this->singlePageUrl = $singlePageUrl;

        if (null === $objTemplate) {
            /** @var FrontendTemplate|object $objTemplate */
            $objTemplate = new FrontendTemplate('textual_pagination');
        }

        $this->objTemplate = $objTemplate;
    }

    /**
     * Generate all page links and return them as array.
     *
     * @return array The page links as array
     */
    public function getItemsAsArray()
    {
        $items = [];

        foreach ($this->teasers as $page => $teaser) {
            if ($page == $this->intPage) {
                $items[] = [
                    'page' => $page,
                    'href' => null,
                    'title' => null,
                    'text' => $teaser,
                ];
            } else {
                $items[] = [
                    'page' => $page,
                    'href' => $this->linkToPage($page),
                    'title' => StringUtil::specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['goToPage'], $page)),
                    'text' => $teaser,
                ];
            }
        }

        if ($this->singlePageUrl) {
            $items[] = [
                'page' => 'singlePage',
                'href' => $this->singlePageUrl,
                'title' => null,
                'text' => $GLOBALS['TL_LANG']['MSC']['readOnSinglePage'],
            ];
        }

        return $items;
    }
}

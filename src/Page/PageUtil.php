<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Page;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\PageModel;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PageUtil
{
    const PAGE_MODEL_TYPE_ROOT = 'root';

    /** @var ContaoFrameworkInterface */
    protected $framework;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->framework = $container->get('contao.framework');
        $this->container = $container;
    }

    public function retrieveGlobalPageFromCurrentPageId(int $id): ?PageModel
    {
        if (null === ($page = $this->container->get('huh.utils.model')->findModelInstanceByPk('tl_page', $id))) {
            return null;
        }

        if (self::PAGE_MODEL_TYPE_ROOT == $page->type) {
            return $page;
        }

        if (null === ($parentPages = $this->framework->getAdapter(PageModel::class)->findParentsById($id))) {
            return $page;
        }

        // get inheritted values from parent pages
        foreach ($parentPages as $parentPage) {
            $diffValues = array_diff_assoc($parentPage->row(), $page->row());

            if (empty($diffValues)) {
                continue;
            }

            foreach ($diffValues as $key => $value) {
                if ($page->{$key}) {
                    continue;
                }
                $page->{$key} = $value;
            }
        }

        // retrieve parameters which don't come from parent pages
        $page->dateFormat = $GLOBALS['TL_CONFIG']['dateFormat'];
        $page->timeFormat = $GLOBALS['TL_CONFIG']['timeFormat'];
        $page->datimFormat = $GLOBALS['TL_CONFIG']['datimFormat'];
        $this->setParametersFromLayout($page);

        return $page;
    }

    protected function setParametersFromLayout(PageModel &$page): void
    {
        if (!$page->layout) {
            return;
        }

        // get values from layout
        if (null === ($layout = $this->container->get('huh.utils.model')->findModelInstanceByPk('tl_layout',
                $page->layout))) {
            return;
        }

        $page->template = $layout->template;
        $page->outputFormat = $layout->doctype;

        // get values from theme
        if (null === ($theme = $this->container->get('huh.utils.model')->findModelInstanceByPk('tl_theme',
                $layout->pid))) {
            return;
        }

        $page->templateGroup = $theme->templates;
    }
}

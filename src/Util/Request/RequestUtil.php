<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util\Request;

use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\PageModel;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestUtil
{
    /** @var ModelUtil */
    protected $modelUtil;
    /**
     * @var RequestStack
     */
    protected $requestStack;
    /**
     * @var array
     */
    protected $kernelPackages;

    public function __construct(ModelUtil $modelUtil, RequestStack $requestStack, array $kernelPackages)
    {
        $this->modelUtil = $modelUtil;
        $this->requestStack = $requestStack;
        $this->kernelPackages = $kernelPackages;
    }

    /**
     * Return the current page model.
     *
     * @See AbstractContentElementController::getPageModel()
     */
    public function getCurrentPageModel(): ?PageModel
    {
        $coreVersion = $this->kernelPackages['contao/core-bundle'] ?? '4.4.0';
        // Contao < 4.9 Fallback
        if (version_compare($coreVersion, '4.9', '<')) {
            if (isset($GLOBALS['objPage']) && $GLOBALS['objPage'] instanceof PageModel) {
                return $GLOBALS['objPage'];
            }

            return null;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (null === $request || !$request->attributes->has('pageModel')) {
            return null;
        }

        $pageModel = $request->attributes->get('pageModel');

        if ($pageModel instanceof PageModel) {
            return $pageModel;
        }

        if (
            isset($GLOBALS['objPage'])
            && $GLOBALS['objPage'] instanceof PageModel
            && (int) $GLOBALS['objPage']->id === (int) $pageModel
        ) {
            return $GLOBALS['objPage'];
        }

        return $this->modelUtil->findModelInstanceByPk('tl_page', (int) $pageModel);
    }
}

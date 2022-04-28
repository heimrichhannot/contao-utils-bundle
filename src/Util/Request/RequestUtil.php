<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
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

    /**
     * Get the base url.
     *
     * @param array $context Pass additional context. Available content: (PageModel) pageModel
     * @param array $options Pass addition options: Available options: (bool) throwException
     */
    public function getBaseUrl(array $context = [], array $options = []): string
    {
        $options = array_merge([
            'throwException' => false,
        ], $options);

        if ($this->requestStack->getCurrentRequest()) {
            return $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost();
        }

        $page = null;

        if (isset($context['pageModel']) && $context['pageModel'] instanceof PageModel) {
            $page = $context['pageModel'];
        }

        if ($page) {
            $url = parse_url(($page->useSSL ? 'https://' : 'http://').$page->getAbsoluteUrl());

            return $url['scheme'].'://'.$url['host'];
        }

        if (true === $options['throwException']) {
            throw new \Exception('Base url could not be determined.');
        }

        return '';
    }

    /**
     * Detect if user already visited our domain before.
     */
    public function isNewVisitor(): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request->headers->has('referer')) {
            return true;
        }
        $referer = $request->headers->get('referer');
        $schemeAndHttpHost = $request->getSchemeAndHttpHost();

        return 1 !== preg_match('$^'.$schemeAndHttpHost.'$i', $referer);
    }
}

<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util\Request;

use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\Model;
use Contao\PageModel;
use HeimrichHannot\UtilsBundle\Util\Model\ModelUtil;
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
     * @return PageModel|Model|null the current page model or null if no page context
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

        return $this->modelUtil->findModelInstanceByPk(PageModel::getTable(), (int) $pageModel);
    }

    /**
     * Return the root page of the current page.
     *
     * @return PageModel|Model|null the root page model or null if not exist
     */
    public function getCurrentRootPageModel(PageModel $currentPage = null): ?PageModel
    {
        if (!$currentPage && !$currentPage = $this->getCurrentPageModel()) {
            return $currentPage;
        }

        $currentPage->loadDetails();

        return $this->modelUtil->findModelInstanceByPk(PageModel::getTable(), $currentPage->rootId);
    }

    /**
     * Get the website base url (scheme and host) considering additional context.
     * If no base url could be determined, an empty string is returned.
     *
     * Context:
     * - (PageModel) pageModel: The current page model
     * - (string) fallback: will be returned if no other base url could be determined
     *
     * Options:
     * - (bool) throwException: Throw exception if no base url could be determined instead of returning empty string
     *
     * @param array $context Pass additional context. Available content: pageModel, fallback
     * @param array $options Pass addition options: Available options: throwException
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

        if (isset($context['fallback']) && \is_string($context['fallback'])) {
            return $context['fallback'];
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

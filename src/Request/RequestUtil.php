<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\UtilsBundle\Request;


use Symfony\Component\HttpFoundation\RequestStack;

class RequestUtil
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * RequestUtil constructor.
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Detect if user already visited our domain before.
     *
     * @return bool
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
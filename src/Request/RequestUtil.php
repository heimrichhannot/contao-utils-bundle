<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Request;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @deprecated Use Utils service instead
 *
 * @codeCoverageIgnore
 */
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
     * @deprecated Use Utils service instead
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

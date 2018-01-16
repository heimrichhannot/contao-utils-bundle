<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\UtilsBundle\String;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;

class StringUtil
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Check for the occurrence at the start of the string.
     *
     * @param $haystack string The string to search in
     * @param $needle   string The needle
     *
     * @return bool
     */
    public function startsWith($haystack, $needle)
    {
        return '' === $needle || false !== strrpos($haystack, $needle, -strlen($haystack));
    }

    public function camelCaseToDashed($value)
    {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $value));
    }
}

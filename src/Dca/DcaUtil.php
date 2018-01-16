<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\UtilsBundle\Dca;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;

class DcaUtil
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Retrieves an array from a dca config (in most cases eval) in the following priorities:.
     *
     * 1. The value associated to $array[$property]
     * 2. The value retrieved by $array[$property . '_callback'] which is a callback array like ['Class', 'method']
     * 3. The value retrieved by $array[$property . '_callback'] which is a function closure array like ['Class', 'method']
     *
     * @param array $array
     * @param       $property
     * @param array $arguments
     *
     * @return mixed|null The value retrieved in the way mentioned above or null
     */
    public function getConfigByArrayOrCallbackOrFunction(array $array, $property, array $arguments = [])
    {
        if (isset($array[$property])) {
            return $array[$property];
        }

        if (is_array($array[$property.'_callback'])) {
            $callback = $array[$property.'_callback'];

            $instance = Controller::importStatic($callback[0]);

            return call_user_func_array([$instance, $callback[1]], $arguments);
        } elseif (is_callable($array[$property.'_callback'])) {
            return call_user_func_array($array[$property.'_callback'], $arguments);
        }

        return null;
    }
}

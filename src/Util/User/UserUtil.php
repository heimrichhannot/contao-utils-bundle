<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util\User;

use HeimrichHannot\UtilsBundle\Traits\PersonTrait;
use HeimrichHannot\UtilsBundle\Util\Model\ModelUtil;

class UserUtil
{
    use PersonTrait;

    const TABLE = 'tl_user';

    /** @var ModelUtil */
    protected $modelUtil;

    /**
     * UserUtil constructor.
     */
    public function __construct(ModelUtil $modelUtil)
    {
        $this->modelUtil = $modelUtil;
    }
}

<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Exception;

class InvalidUrlException extends \Exception
{
    protected $message = 'Given url is invalid';
}

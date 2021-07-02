<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util;

use HeimrichHannot\UtilsBundle\Traits\UtilsTrait;

if (interface_exists('Symfony\Contracts\Service\ServiceSubscriberInterface')) {
    class Utils implements \Symfony\Contracts\Service\ServiceSubscriberInterface
    {
        use UtilsTrait;
    }
} elseif (interface_exists('Symfony\Component\DependencyInjection\ServiceSubscriberInterface')) {
    class Utils implements \Symfony\Component\DependencyInjection\ServiceSubscriberInterface
    {
        use UtilsTrait;
    }
}

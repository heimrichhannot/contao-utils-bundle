<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util;

use Symfony\Component\DependencyInjection\ServiceSubscriberInterface as LegacyServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

if (interface_exists(ServiceSubscriberInterface::class)) {
    abstract class AbstractServiceSubscriber implements ServiceSubscriberInterface
    {
    }
} elseif (interface_exists(LegacyServiceSubscriberInterface::class)) {
    abstract class AbstractServiceSubscriber implements LegacyServiceSubscriberInterface
    {
    }
}

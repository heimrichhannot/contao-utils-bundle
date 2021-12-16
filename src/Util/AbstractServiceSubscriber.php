<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util;

/**
 * @codeCoverageIgnore
 */
if (interface_exists('Symfony\Component\DependencyInjection\ServiceSubscriberInterface')) {
    abstract class AbstractServiceSubscriber implements \Symfony\Component\DependencyInjection\ServiceSubscriberInterface
    {
    }
} elseif (interface_exists('Symfony\Contracts\Service\ServiceSubscriberInterface')) {
    abstract class AbstractServiceSubscriber implements \Symfony\Contracts\Service\ServiceSubscriberInterface
    {
    }
}

<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

if (class_exists('Symfony\Component\EventDispatcher\Event')) {
    abstract class AbstractEvent extends \Symfony\Component\EventDispatcher\Event
    {
    }
} elseif (class_exists(Event::class)) {
    abstract class AbstractEvent extends Event
    {
    }
}

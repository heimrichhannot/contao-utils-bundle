<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle;

use HeimrichHannot\UtilsBundle\DependencyInjection\UtilsBundleExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotContaoUtilsBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new UtilsBundleExtension();
    }
}

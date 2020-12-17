<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle;

use HeimrichHannot\UtilsBundle\DependencyInjection\HeimrichHannotUtilsExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotUtilsBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new HeimrichHannotUtilsExtension();
    }
}

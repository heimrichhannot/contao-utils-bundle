<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests;

use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\MockObject\MockBuilder;

abstract class AbstractUtilsTestCase extends ContaoTestCase
{
    abstract public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null);
}

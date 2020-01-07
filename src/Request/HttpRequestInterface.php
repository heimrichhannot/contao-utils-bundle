<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Request;

interface HttpRequestInterface
{
    public function init($url): self;

    public function setOption($name, $value): self;

    public function execute();

    public function getInfo($name);

    public function close();
}

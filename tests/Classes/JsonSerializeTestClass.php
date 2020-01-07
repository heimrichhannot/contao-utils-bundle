<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Classes;

class JsonSerializeTestClass
{
    public $publicVar;

    protected $protectedVar;

    private $privateVar;

    public function __construct()
    {
        $this->privateVar = 'test';
    }

    public function getMap()
    {
        return ['map' => true];
    }

    public function getMapWithAttributes(array $attributes = [])
    {
        return ['map' => true];
    }

    public function getNestedObject()
    {
        return new \stdClass();
    }

    public function isPublished()
    {
        return true;
    }

    public function hasPublished()
    {
        return false;
    }

    public function isAddDetails()
    {
        return true;
    }

    protected function getProtectedMap()
    {
        return ['protected_map' => true];
    }
}

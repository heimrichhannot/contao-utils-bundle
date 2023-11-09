<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Twig;

use Contao\Validator;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class TestExtension extends AbstractExtension implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @return array|TwigTest[]
     */
    public function getTests()
    {
        return [
            new TwigTest('numeric', [$this, 'isNumeric'], ['deprecated' => true]),
            new TwigTest('float', [$this, 'isFloat'], ['deprecated' => true]),
            new TwigTest('string', [$this, 'isString'], ['deprecated' => true]),
            new TwigTest('object', [$this, 'isObject'], ['deprecated' => true]),
            new TwigTest('array', [$this, 'isArray'], ['deprecated' => true]),
            new TwigTest('bool', [$this, 'isBool'], ['deprecated' => true]),
            new TwigTest('int', [$this, 'isInt'], ['deprecated' => true]),
            new TwigTest('uuid', [$this, 'isUuid'], ['deprecated' => true]),
            new TwigTest('binaryUuid', [$this, 'isBinaryUuid'], ['deprecated' => true]),
            new TwigTest('stringUuid', [$this, 'isStringUuid'], ['deprecated' => true]),
            new TwigTest('url', [$this, 'isUrl'], ['deprecated' => true]),
            new TwigTest('email', [$this, 'isEmail'], ['deprecated' => true]),
        ];
    }

    public function isNumeric($value): bool
    {
        return is_numeric($value);
    }

    public function isFloat($value): bool
    {
        return \is_float($value);
    }

    public function isString($value): bool
    {
        return \is_string($value);
    }

    public function isObject($value): bool
    {
        return \is_object($value);
    }

    public function isArray($value): bool
    {
        return \is_array($value);
    }

    public function isBool($value): bool
    {
        return \is_bool($value);
    }

    public function isInt($value): bool
    {
        return \is_int($value);
    }

    public function isUuid($value): bool
    {
        return Validator::isUuid($value);
    }

    public function isBinaryUuid($value): bool
    {
        return Validator::isBinaryUuid($value);
    }

    public function isStringUuid($value): bool
    {
        return Validator::isStringUuid($value);
    }

    public function isUrl($value): bool
    {
        return Validator::isUrl($value);
    }

    public function isEmail($value): bool
    {
        return Validator::isEmail($value);
    }
}

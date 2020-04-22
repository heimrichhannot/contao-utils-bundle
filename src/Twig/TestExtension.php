<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Twig;

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
            new TwigTest('numeric', [$this, 'isNumeric']),
            new TwigTest('float', [$this, 'isFloat']),
            new TwigTest('string', [$this, 'isString']),
            new TwigTest('object', [$this, 'isObject']),
            new TwigTest('array', [$this, 'isArray']),
            new TwigTest('bool', [$this, 'isBool']),
            new TwigTest('int', [$this, 'isInt']),
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
}

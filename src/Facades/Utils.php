<?php

/**
 * Facade class for accessing utility classes in HeimrichHannot\UtilsBundle.
 *
 * @author Eric Gesemann <e.gesemann@heimrich-hannot.de>
 * @copyright Copyright (c) 2024, Heimrich & Hannot GmbH
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Facades;

use Contao\System;
use HeimrichHannot\UtilsBundle\Util\Utils as UtilsClass;
use RuntimeException;

/**
 * @noinspection PhpFullyQualifiedNameUsageInspection
 *
 * @method static \HeimrichHannot\UtilsBundle\Util\AccordionUtil accordion()
 * @method static \HeimrichHannot\UtilsBundle\Util\AnonymizeUtil anonymize()
 * @method static \HeimrichHannot\UtilsBundle\Util\ArrayUtil array()
 * @method static \HeimrichHannot\UtilsBundle\Util\ClassUtil class()
 * @method static \HeimrichHannot\UtilsBundle\Util\ContainerUtil container()
 * @method static \HeimrichHannot\UtilsBundle\Util\DatabaseUtil database()
 * @method static \HeimrichHannot\UtilsBundle\Util\DcaUtil dca()
 * @method static \HeimrichHannot\UtilsBundle\Util\FileUtil file()
 * @method static \HeimrichHannot\UtilsBundle\Util\HtmlUtil html()
 * @method static \HeimrichHannot\UtilsBundle\Util\LocaleUtil locale()
 * @method static \HeimrichHannot\UtilsBundle\Util\ModelUtil model()
 * @method static \HeimrichHannot\UtilsBundle\Util\RequestUtil request()
 * @method static \HeimrichHannot\UtilsBundle\Util\RoutingUtil routing()
 * @method static \HeimrichHannot\UtilsBundle\Util\StringUtil string()
 * @method static \HeimrichHannot\UtilsBundle\Util\UrlUtil url()
 * @method static \HeimrichHannot\UtilsBundle\Util\UserUtil user()
 */
class Utils
{
    protected static UtilsClass $root;

    public function __call(string $name, array $arguments)
    {
        return static::getFacadeRoot()->$name(...$arguments);
    }

    public static function __callStatic(string $name, array $arguments)
    {
        return static::getFacadeRoot()->$name(...$arguments);
    }

    public static function getFacadeRoot(): UtilsClass
    {
        if (!isset(static::$root))
        {
            static::$root = System::getContainer()->get(UtilsClass::class);

            if (!static::$root instanceof UtilsClass)
            {
                throw new RuntimeException(
                    'Facade root is not an instance of ' . UtilsClass::class
                );
            }
        }

        return static::$root;
    }
}
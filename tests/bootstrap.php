<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

error_reporting(E_ALL);

$include = function ($file) {
    return file_exists($file) ? include $file : false;
};

// PhpStorm fix (see https://www.drupal.org/node/2597814)
if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
    define('PHPUNIT_COMPOSER_INSTALL', __DIR__.'/../vendor/autoload.php');
}

if (false === ($loader = $include(__DIR__.'/../vendor/autoload.php'))
    && false === ($loader = $include(__DIR__.'/../../../autoload.php'))
    && false === ($loader = $include(dirname(dirname(getenv('PWD'))).'/autoload.php'))) {
    echo 'You must set up the project dependencies, run the following commands:'.PHP_EOL.'curl -sS https://getcomposer.org/installer | php'.PHP_EOL.'php composer.phar install'.PHP_EOL;

    exit(1);
}

require 'TestCaseEnvironment.php';

require 'Request/StubCurlRequest.php';

// Handle classes in the global namespace
$legacyLoader = function ($class) {
    if (class_exists($class, false) || interface_exists($class, false) || trait_exists($class, false)) {
        return;
    }

    if (false !== strpos($class, '\\') && 0 !== strncmp($class, 'Contao\\', 7)) {
        return;
    }

    if (0 === strncmp($class, 'Contao\\', 7)) {
        $class = substr($class, 7);
    }

    $namespaced = 'Contao\\'.$class;

    if (class_exists($namespaced) || interface_exists($namespaced) || trait_exists($namespaced)) {
        if (!class_exists($class, false) && !interface_exists($class, false) && !trait_exists($class, false)) {
            class_alias($namespaced, $class);
        }
    }
};

spl_autoload_register($legacyLoader, true, true);

return $loader;

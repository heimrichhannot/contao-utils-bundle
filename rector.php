<?php

declare(strict_types=1);

use Contao\Rector\Set\ContaoSetList;
use Rector\Config\RectorConfig;
use Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector;
use Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withPhpVersion(PhpVersion::PHP_84)
    ->withRules([
        AddVoidReturnTypeWhereNoReturnRector::class,
        # In Vorbereitung für PHP 8.4:
         ExplicitNullableParamTypeRector::class
    ])

    ->withImportNames(
        importShortClasses: false,
        removeUnusedImports: true,
    )
    ->withComposerBased(
        twig: true,
        doctrine: true,
        phpunit: true,
        symfony: true,
    )
    ->withSets([
        LevelSetList::UP_TO_PHP_81,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        # Die ContaoLevelSetList nutzt Symfony-Sets, die es in Rector 2 nicht mehr gibt.
        # Die Symfony-Regeln kommen über withComposerBased().
        ContaoSetList::CONTAO_49,
        ContaoSetList::CONTAO_413,
        ContaoSetList::CONTAO_50,
        ContaoSetList::CONTAO_51,
        ContaoSetList::CONTAO_53,
        ContaoSetList::FQCN,
        ContaoSetList::ANNOTATIONS_TO_ATTRIBUTES,
    ])
    ->withSkip([
        ArrayToFirstClassCallableRector::class,
    ])



    ;
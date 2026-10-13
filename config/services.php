<?php

use HeimrichHannot\UtilsBundle\SearchVector\Normalizer\NormalizerInterface;
use HeimrichHannot\UtilsBundle\SearchVector\Stemmer\StemmerInterface;
use HeimrichHannot\UtilsBundle\Util\ContainerUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure();

    $services->set(Utils::class)
        ->public();

    $services->load('HeimrichHannot\\UtilsBundle\\', '../src/{Command,EntityFinder,EventListener,SearchVector,Twig,Util}/*')
        ->exclude([
            '../src/EntityFinder/Element.php',
            '../src/Util/Utils.php',
            '../src/SearchVector/SearchVectorDataContainer.php',
        ])
        ->bind('$projectDir', param('kernel.project_dir'))
        ->bind('$csrfTokenName', param('contao.csrf_token_name'))
        ->bind('$kernelBundles', param('kernel.bundles'))
        ->bind('$defaultLocale', param('kernel.default_locale'))
        ->bind('$searchVectorStemmers', tagged_locator(StemmerInterface::TAG))
        ->bind('$searchVectorNormalizers', tagged_locator(NormalizerInterface::TAG));

    $services->set(ContainerUtil::class)
        ->tag('container.service_subscriber', ['key' => 'monolog.logger.contao', 'id' => 'monolog.logger.contao']);
};

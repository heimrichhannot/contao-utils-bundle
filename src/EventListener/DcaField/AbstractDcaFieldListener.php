<?php

namespace HeimrichHannot\UtilsBundle\EventListener\DcaField;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Model;
use HeimrichHannot\UtilsBundle\Dca\DcaFieldConfiguration;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

abstract class AbstractDcaFieldListener implements ServiceSubscriberInterface
{
    /** @var ContainerInterface */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    protected function getModelInstance(string $table, int $id): ?Model
    {
        $framework = $this->container->get('contao.framework');
        $modelClass = $framework->getAdapter(Model::class)->getClassFromTable($table);
        return $framework->getAdapter($modelClass)->findByPk($id);
    }

    protected function applyDefaultFieldAdjustments(array &$field, DcaFieldConfiguration $configuration)
    {
        if ($configuration->isFilter()) {
            $field['filter'] = true;
        }

        if ($configuration->isSearch()) {
            $field['search'] = true;
        }

        if ($configuration->isExclude()) {
            $field['exclude'] = true;
        }

        if ($configuration->isSorting()) {
            $field['sorting'] = true;
        }

        if ($configuration->getFlag() !== null) {
            $field['flag'] = $configuration->getFlag();
        }
    }

    public static function getSubscribedServices(): array
    {
        return [
            'contao.framework' => ContaoFramework::class,
        ];
    }
}
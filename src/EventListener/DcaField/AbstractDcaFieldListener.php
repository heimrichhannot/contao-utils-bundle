<?php

namespace HeimrichHannot\UtilsBundle\EventListener\DcaField;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Model;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

abstract class AbstractDcaFieldListener implements ServiceSubscriberInterface
{
    private ContainerInterface $container;

    protected function getModelInstance(string $table, int $id): ?Model
    {
        $framework = $this->container->get('contao.framework');
        $modelClass = $framework->getAdapter(Model::class)->getClassFromTable($table);
        return $framework->getAdapter($modelClass)->findByPk($id);
    }

    public static function getSubscribedServices(): array
    {
        return [
            'contao.framework' => ContaoFramework::class,
        ];
    }

    public function setContainer(ContainerInterface $container): ?ContainerInterface
    {
        $this->container = $container;
    }


}
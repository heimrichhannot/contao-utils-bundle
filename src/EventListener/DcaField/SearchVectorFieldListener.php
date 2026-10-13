<?php

namespace HeimrichHannot\UtilsBundle\EventListener\DcaField;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\DataContainer;
use HeimrichHannot\UtilsBundle\Dca\SearchVectorField;
use HeimrichHannot\UtilsBundle\SearchVector\SearchVectorIndexer;

class SearchVectorFieldListener extends AbstractDcaFieldListener
{
    #[AsHook('loadDataContainer')]
    public function onLoadDataContainer(string $table): void
    {
        if (!$configuration = SearchVectorField::getRegistrations()[$table] ?? null) {
            return;
        }

        // Machine-written, so it is part of no palette and hidden from the "show" view and the version diff
        $field = [
            'eval' => ['doNotShow' => true],
            'sql' => ['type' => 'text', 'notnull' => false],
        ];

        $this->applyDefaultFieldAdjustments($field, $configuration);

        $GLOBALS['TL_DCA'][$table]['fields'][$configuration->getFieldName()] = $field;

        // The table is only known at runtime, so the callbacks cannot be registered with attributes
        $GLOBALS['TL_DCA'][$table]['config']['onsubmit_callback'][] = [self::class, 'onSubmitCallback'];
        $GLOBALS['TL_DCA'][$table]['config']['oncopy_callback'][] = [self::class, 'onCopyCallback'];
        $GLOBALS['TL_DCA'][$table]['config']['onrestore_version_callback'][] = [self::class, 'onRestoreVersionCallback'];
    }

    public function onSubmitCallback(DataContainer $dc): void
    {
        if ($dc->id) {
            $this->container->get(SearchVectorIndexer::class)->index($dc->table, (int) $dc->id, $dc);
        }
    }

    public function onCopyCallback(int|string $insertId, DataContainer $dc): void
    {
        $this->container->get(SearchVectorIndexer::class)->index($dc->table, (int) $insertId);
    }

    public function onRestoreVersionCallback(string $table, int|string $id, int|string $version, array $data): void
    {
        $this->container->get(SearchVectorIndexer::class)->index($table, (int) $id);
    }

    public static function getSubscribedServices(): array
    {
        $services = parent::getSubscribedServices();
        $services[] = SearchVectorIndexer::class;

        return $services;
    }
}

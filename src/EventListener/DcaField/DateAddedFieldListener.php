<?php

namespace HeimrichHannot\UtilsBundle\EventListener\DcaField;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\DataContainer;
use HeimrichHannot\UtilsBundle\Dca\DateAddedField;

class DateAddedFieldListener extends AbstractDcaFieldListener
{
    /**
     * @Hook("loadDataContainer")
     */
    public function onLoadDataContainer(string $table): void
    {
        if (!isset(DateAddedField::getRegistrations()[$table])) {
            return;
        }

        $GLOBALS['TL_DCA'][$table]['config']['onload_callback'][] = [self::class, 'onLoadCallback'];
        $GLOBALS['TL_DCA'][$table]['config']['oncopy_callback'][] = [self::class, 'onCopyCallback'];

        $GLOBALS['TL_DCA'][$table]['fields']['dateAdded'] = [
            'label'   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'eval'    => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql'     => "int(10) unsigned NOT NULL default '0'",
        ];
    }

    public function onLoadCallback(DataContainer $dc = null): void
    {
        if (!$dc || !$dc->id) {
            return;
        }

        $model = $this->getModelInstance($dc->table, (int)$dc->id);
        if (!$model || $model->dateAdded > 0) {
            return;
        }

        $model->dateAdded = time();
        $model->save();
    }

    public function onCopyCallback(int $insertId, DataContainer $dc): void
    {
        if (!$dc || !$dc->id) {
            return;
        }

        $model = $this->getModelInstance($dc->table, $insertId);
        if (!$model || $model->dateAdded > 0) {
            return;
        }

        $model->dateAdded = time();
        $model->save();
    }
}
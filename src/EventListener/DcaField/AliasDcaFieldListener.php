<?php

namespace HeimrichHannot\UtilsBundle\EventListener\DcaField;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Slug\Slug;
use Contao\Database;
use Contao\DataContainer;
use HeimrichHannot\UtilsBundle\Dca\AliasField;
use HeimrichHannot\UtilsBundle\Dca\AliasFieldConfiguration;

class AliasDcaFieldListener extends AbstractDcaFieldListener
{
    #[AsHook('loadDataContainer')]
    public function onLoadDataContainer(string $table): void
    {
        if (!isset(AliasField::getRegistrations()[$table])) {
            return;
        }

        /** @var AliasFieldConfiguration $registration */
        $registration = AliasField::getRegistrations()[$table];

        $field = AliasField::getField();
        if (is_array($registration->aliasExistCallback)) {
            $field['save_callback'][] = $registration->aliasExistCallback;
        }

        $this->applyDefaultFieldAdjustments($field, $registration);

        $GLOBALS['TL_DCA'][$table]['fields'][$registration->fieldName] = $field;
    }

    public function onFieldsAliasSaveCallback($value, DataContainer $dc)
    {
        $framework = $this->container->get('contao.framework');
        $aliasExists = (static fn(string $alias): bool => $framework->createInstance(Database::class)
                ->prepare("SELECT id FROM $dc->table WHERE alias=? AND id!=?")
                ->execute($alias, $dc->id)
                ->numRows > 0);

        // Generate an alias if there is none
        if (!$value) {
            $value = $this->container->get('contao.slug')->generate(
                /** @phpstan-ignore property.notFound */
                (string)$dc->activeRecord->title,
                /** @phpstan-ignore property.notFound */
                (int)$dc->activeRecord->pid,
                $aliasExists
            );
        } elseif (preg_match('/^[1-9]\d*$/', (string) $value)) {
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasNumeric'], $value));
        } elseif ($aliasExists($value)) {
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $value));
        }

        return $value;
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(
            [
                'contao.slug' => Slug::class,
                'contao.framework' => ContaoFramework::class,
            ],
            parent::getSubscribedServices()
        );
    }#


}
<?php

namespace HeimrichHannot\UtilsBundle\EventListener;

use Contao\BackendUser;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\DataContainer;
use Contao\FrontendUser;
use Contao\Model;
use HeimrichHannot\UtilsBundle\Dca\AuthorField;
use Symfony\Component\Security\Core\Security;

class DcaAuthorListener
{
    private ContaoFramework $framework;
    private Security $security;

    public function __construct(ContaoFramework $framework, Security $security)
    {
        $this->framework = $framework;
        $this->security = $security;
    }


    /**
     * @Hook("loadDataContainer")
     */
    public function onLoadDataContainer(string $table): void
    {
        if (!isset(AuthorField::getRegistrations()[$table])) {
            return;
        }

        $options = $this->getOptions($table);

        $authorFieldName = empty($options['fieldNamePrefix']) ? 'author' : $options['fieldNamePrefix'].'Author';

        $authorField = [
            'exclude' => $options['exclude'],
            'search' => $options['search'],
            'filter' => $options['filter'],
            'inputType' => 'select',
            'eval' => [
                'doNotCopy' => true,
                'mandatory' => true,
                'chosen' => true,
                'includeBlankOption' => true,
                'tl_class' => 'w50'
            ],
            'sql' => "int(10) unsigned NOT NULL default 0",
        ];

        if ($options['useDefaultLabel']) {
            $authorField['label'] = &$GLOBALS['TL_LANG']['MSC']['utilsBundle']['author'];
        }

        if (AuthorField::TYPE_USER === $options['type']) {
            $authorField['default'] = BackendUser::getInstance()->id;
            $authorField['foreignKey'] = 'tl_user.name';
            $authorField['relation'] = ['type'=>'hasOne', 'load'=>'lazy'];
        } elseif (AuthorField::TYPE_MEMBER === $options['type']) {
            $authorField['default'] = FrontendUser::getInstance()->id;
            $authorField['foreignKey'] = "tl_member.CONCAT(firstname,' ',lastname)";
            $authorField['relation'] = ['type'=>'hasOne', 'load'=>'lazy'];
        } else {
            $authorField['default'] = 0;
        }

        $GLOBALS['TL_DCA'][$table]['fields'][$authorFieldName] = $authorField;
        $GLOBALS['TL_DCA'][$table]['config']['oncopy_callback'][] = [self::class, 'onConfigCopyCallback'];
    }


    public function onConfigCopyCallback(int $insertId, DataContainer $dc): void
    {
        $options = $this->getOptions($dc->table);
        $authorFieldName = empty($options['fieldNamePrefix']) ? 'author' : $options['fieldNamePrefix'].'Author';

        /** @var class-string<Model> $modelClass */
        $modelClass = $this->framework->getAdapter(Model::class)->getClassFromTable($dc->table);
        $model = $modelClass::findByPk($insertId);
        if (!$model) {
            return;
        }

        if (AuthorField::TYPE_USER === $options['type']) {
            $model->{$authorFieldName} = BackendUser::getInstance()->id;
        } elseif (AuthorField::TYPE_MEMBER === $options['type']) {
            $model->{$authorFieldName} = FrontendUser::getInstance()->id;
        } else {
            $model->{$authorFieldName} = 0;
        }
    }

    /**
     * @param string $table
     * @return array|bool[]|string[]
     */
    protected function getOptions(string $table): array
    {
        return array_merge([
            'type' => AuthorField::TYPE_USER,
            'fieldNamePrefix' => '',
            'useDefaultLabel' => true,
            'exclude' => true,
            'search' => true,
            'filter' => true,
        ], AuthorField::getRegistrations()[$table]);
    }
}
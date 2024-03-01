<?php

namespace HeimrichHannot\UtilsBundle\EventListener\DcaField;

use Contao\BackendUser;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\DataContainer;
use Contao\FrontendUser;
use Contao\Model;
use HeimrichHannot\UtilsBundle\Dca\AuthorField;
use HeimrichHannot\UtilsBundle\Dca\AuthorFieldConfiguration;
use Symfony\Component\Security\Core\Security;

class DcaAuthorListener
{
    /** @var ContaoFramework  */
    private $framework;
    /** @var Security  */
    private $security;

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

        $options = AuthorField::getRegistrations()[$table];

        $authorFieldName = $this->getAuthorFieldName($options);

        $authorField = [
            'exclude' => $options->isExclude(),
            'search' => $options->isSearch(),
            'filter' => $options->isFilter(),
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

        if ($options->isUseDefaultLabel()) {
            $authorField['label'] = &$GLOBALS['TL_LANG']['MSC']['utilsBundle']['author'];
        }

        $authorField['default'] = 0;
        if (AuthorField::TYPE_USER === $options->getType()) {
            if ($this->security->getUser() instanceof BackendUser) {
                $authorField['default'] = $this->security->getUser()->id;
            }
            $authorField['foreignKey'] = 'tl_user.name';
            $authorField['relation'] = ['type'=>'hasOne', 'load'=>'lazy'];
        } elseif (AuthorField::TYPE_MEMBER === $options->getType()) {
            if ($this->security->getUser() instanceof FrontendUser) {
                $authorField['default'] = $this->security->getUser()->id;
            }
            $authorField['foreignKey'] = "tl_member.CONCAT(firstname,' ',lastname)";
            $authorField['relation'] = ['type'=>'hasOne', 'load'=>'lazy'];
        }

        $GLOBALS['TL_DCA'][$table]['fields'][$authorFieldName] = $authorField;
        $GLOBALS['TL_DCA'][$table]['config']['oncopy_callback'][] = [self::class, 'onConfigCopyCallback'];
    }


    public function onConfigCopyCallback(int $insertId, DataContainer $dc): void
    {
        $options = AuthorField::getRegistrations()[$dc->table];
        $authorFieldName = $this->getAuthorFieldName($options);

        /** @var class-string<Model> $modelClass */
        $modelClass = $this->framework->getAdapter(Model::class)->getClassFromTable($dc->table);
        $model = $this->framework->getAdapter($modelClass)->findByPk($insertId);
        if (!$model) {
            return;
        }

        $model->{$authorFieldName} = 0;
        if (AuthorField::TYPE_USER === $options->getType()) {
            if ($this->security->getUser() instanceof BackendUser) {
                $model->{$authorFieldName} = $this->security->getUser()->id;
            }
        } elseif (AuthorField::TYPE_MEMBER === $options->getType()) {
            if ($this->security->getUser() instanceof FrontendUser) {
                $model->{$authorFieldName} = $this->security->getUser()->id;
            }
        }
        $model->save();
    }

    /**
     * @param AuthorFieldConfiguration $options
     * @return string
     */
    protected function getAuthorFieldName(AuthorFieldConfiguration $options): string
    {
        if (!$options->hasFieldNamePrefix()) {
            return 'author';
        }
        if (str_ends_with($options->getFieldNamePrefix(), '_')) {
            return $options->getFieldNamePrefix() . 'author';
        } else {
            return $options->getFieldNamePrefix() . 'Author';
        }
    }
}
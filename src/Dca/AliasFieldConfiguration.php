<?php

namespace HeimrichHannot\UtilsBundle\Dca;

use HeimrichHannot\UtilsBundle\EventListener\DcaField\AliasDcaFieldListener;

class AliasFieldConfiguration extends DcaFieldConfiguration
{
    public ?array $aliasExistCallback = [AliasDcaFieldListener::class, 'onFieldsAliasSaveCallback'];

    public string $fieldName = 'alias';

    /**
     * Override the default alias exist function. Provide as [Class, 'method'].
     *
     * @param array<string, string> $aliasExistCallback
     */
    public function setAliasExistCallback(?array $aliasExistCallback): AliasFieldConfiguration
    {
        $this->aliasExistCallback = $aliasExistCallback;
        return $this;
    }

    public function setFieldName(string $fieldName): AliasFieldConfiguration
    {
        $this->fieldName = $fieldName;
        return $this;
    }
}
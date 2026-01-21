<?php

namespace HeimrichHannot\UtilsBundle\Dca;

use HeimrichHannot\UtilsBundle\EventListener\DcaField\AliasDcaFieldListener;

class AliasFieldConfiguration extends DcaFieldConfiguration
{
    /**
     * @internal
     * @deprecated
     */
    public ?array $aliasExistCallback = [AliasDcaFieldListener::class, 'onFieldsAliasSaveCallback'];

    /**
     * @internal
     */
    public string $fieldName = 'alias';

    /**
     * @internal
     */
    public string $titleField = 'title';

    /**
     * @internal
     */
    public ?array $generateAliasCallback = [AliasDcaFieldListener::class, 'onFieldsAliasSaveCallback'];

    /**
     * Override the default alias exist function. Provide as [Class, 'method'].
     *
     * @param array<string, string> $aliasExistCallback
     * @deprecated Deprecated since version 3.10. Use setGenerateAliasCallback instead.
     */
    public function setAliasExistCallback(?array $aliasExistCallback): AliasFieldConfiguration
    {
        $this->generateAliasCallback = $aliasExistCallback;
        return $this;
    }

    /**
     * Override the default alias exist function. Provide as [Class, 'method'].
     *
     * @param array<string, string> $aliasExistCallback
     */
    public function setGenerateAliasCallback(?array $aliasExistCallback): AliasFieldConfiguration
    {
        $this->generateAliasCallback = $aliasExistCallback;
        return $this;
    }

    public function setFieldName(string $fieldName): AliasFieldConfiguration
    {
        $this->fieldName = $fieldName;
        return $this;
    }

    public function setTitleField(string $titleField): AliasFieldConfiguration
    {
        $this->titleField = $titleField;
        return $this;
    }
}
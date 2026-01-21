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
     * @param array<string, string> $aliasExistCallback
     * @deprecated Deprecated since version 3.10. Use setGenerateAliasCallback instead.
     */
    public function setAliasExistCallback(?array $aliasExistCallback): AliasFieldConfiguration
    {
        $this->generateAliasCallback = $aliasExistCallback;
        return $this;
    }

    /**
     * Override the default alias generation function. Provide as [Class, 'method'].
     *
     * @param array<string, string> $callback
     */
    public function setGenerateAliasCallback(?array $callback): AliasFieldConfiguration
    {
        $this->generateAliasCallback = $callback;
        return $this;
    }

    public function setFieldName(string $fieldName): AliasFieldConfiguration
    {
        $this->fieldName = $fieldName;
        return $this;
    }

    /**
     * Set the field name from which the alias should be generated.
     */
    public function setTitleField(string $titleField): AliasFieldConfiguration
    {
        $this->titleField = $titleField;
        return $this;
    }
}
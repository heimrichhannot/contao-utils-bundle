<?php

namespace HeimrichHannot\UtilsBundle\Dca;

class AuthorFieldConfiguration extends DcaFieldConfiguration
{
    /** @var string  */
    protected string $type = AuthorField::TYPE_USER;
    /** @var string  */
    protected string $fieldNamePrefix = '';
    /** @var bool  */
    protected bool $useDefaultLabel = true;
    /** @var bool  */
    protected bool $exclude = true;
    /** @var bool  */
    protected bool $search = true;
    /** @var bool  */
    protected bool $filter = true;

    public function setType(string $type): AuthorFieldConfiguration
    {
        $this->type = $type;
        return $this;
    }

    public function hasFieldNamePrefix(): bool
    {
        return !empty($this->fieldNamePrefix);
    }

    public function getFieldNamePrefix(): string
    {
        return $this->fieldNamePrefix;
    }

    public function setFieldNamePrefix(string $fieldNamePrefix): AuthorFieldConfiguration
    {
        $this->fieldNamePrefix = $fieldNamePrefix;
        return $this;
    }

    public function isUseDefaultLabel(): bool
    {
        return $this->useDefaultLabel;
    }

    public function setUseDefaultLabel(bool $useDefaultLabel): AuthorFieldConfiguration
    {
        $this->useDefaultLabel = $useDefaultLabel;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
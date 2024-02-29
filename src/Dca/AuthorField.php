<?php

namespace HeimrichHannot\UtilsBundle\Dca;

class AuthorField extends AbstractDcaField
{
    public const TYPE_USER = 'user';
    public const TYPE_MEMBER = 'member';

    /**
     * @return array<AuthorFieldOptions>
     */
    public static function getRegistrations(): array
    {
        return parent::getRegistrations();
    }

    /**
     * @param string $table
     * @return AuthorFieldOptions
     */
    protected static function createOptionObject(string $table): DcaFieldOptions
    {
        return new AuthorFieldOptions($table);
    }


}
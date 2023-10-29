<?php

namespace HeimrichHannot\UtilsBundle\Util;

use HeimrichHannot\UtilsBundle\Util\DatabaseUtil\CreateWhereForSerializedBlobResult;

class DatabaseUtil
{
    /**
     * Create a where condition for a field that contains a serialized blob.
     *
     * @param string $field A field containing a serialized array.
     * @param array $values The values that should be searched for in the field.
     */
    public function createWhereForSerializedBlob(string $field, array $values): CreateWhereForSerializedBlobResult
    {
        return new CreateWhereForSerializedBlobResult($field, $values);
    }
}
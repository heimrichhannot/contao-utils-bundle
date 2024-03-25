<?php

namespace HeimrichHannot\UtilsBundle\Util\FormatterUtil;

use HeimrichHannot\UtilsBundle\Options\OptionsFactory;

/**
 * @method $this setPreserveEmptyArrayValues(bool $value)
 * @method $this setLocalize(bool $value)
 * @method $this setLoadDca(bool $value)
 * @method $this setCacheOptions(bool $value)
 * @method $this setReplaceInsertTags(bool $value)
 */
class FormatDcaFieldValueOptions extends OptionsFactory
{
    public bool $preserveEmptyArrayValues = false;
    public bool $localize = true;
    public bool $loadDca = true;
    public bool $cacheOptions = true;
    public bool $replaceInsertTags = true;
}
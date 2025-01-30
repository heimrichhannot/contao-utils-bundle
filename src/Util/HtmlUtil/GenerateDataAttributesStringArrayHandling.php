<?php

namespace HeimrichHannot\UtilsBundle\Util\HtmlUtil;

if (version_compare(phpversion(), '8.1','>=')) {
    enum GenerateDataAttributesStringArrayHandling: string
    {
        case REDUCE = 'reduce';
        case ENCODE = 'encode';
    }
}
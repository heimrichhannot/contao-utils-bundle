<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\UtilsBundle\String;

/**
 * Class AnonymizerUtil
 * @package HeimrichHannot\UtilsBundle\String
 *
 * Service alias: huh.utils.string
 */
class AnonymizerUtil
{
    /**
     * Returns an ananymized email address.
     *
     * If the mail address is not valid (this function only check if the string contains an @), the source string is returned.
     *
     * Example:
     * max.mustermann@example.org will be max.mus*******\@example.org
     *
     * @param string $email
     * @return string The anonymized email address
     * @since 2.131
     */
    public function anonymizeEmail(string $email)
    {
        $em   = explode("@",$email);
        if (count($em) !== 2) {
            return $email;
        }
        $name = implode('@', array_slice($em, 0, count($em)-1));
        $len  = floor(strlen($name)/2);
        return substr($name,0, $len) . str_repeat('*', $len) . "@" . end($em);
    }
}
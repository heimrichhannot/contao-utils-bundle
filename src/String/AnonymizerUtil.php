<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\String;

/**
 * Class AnonymizerUtil.
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
     * @return string The anonymized email address
     *
     * @since 2.131
     */
    public function anonymizeEmail(string $email)
    {
        $em = explode('@', $email);

        if (2 !== \count($em)) {
            return $email;
        }
        $name = implode('@', \array_slice($em, 0, \count($em) - 1));
        $len = floor(\strlen($name) / 2);

        return substr($name, 0, $len).str_repeat('*', $len).'@'.end($em);
    }
}

<?php

namespace HeimrichHannot\UtilsBundle\Util\Data;

class AnonymizeUtil
{
    /**
     * Returns an anonymized email address.
     *
     * If the mail address is not valid (this function only check if the string contains an @), the source string is returned.
     *
     * Example:
     * max.mustermann@example.org will be max.mus*******\@example.org
     *
     * @return string The anonymized email address
     */
    public function anonymizeEmail(string $email): string
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
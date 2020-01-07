<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Security;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;

class EncryptionUtil
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    public function encrypt(string $plain, string $key = '', string $cipher = 'aes-256-ctr', $options = 0)
    {
        $key = '' !== $key ? $key : System::getContainer()->getParameter('secret');

        if (\in_array($cipher, openssl_get_cipher_methods())) {
            $ivLength = openssl_cipher_iv_length($cipher);
            $iv = openssl_random_pseudo_bytes($ivLength);

            return [openssl_encrypt($plain, $cipher, $key, $options, $iv), base64_encode($iv)];
        }

        return false;
    }

    public function decrypt(string $encrypted, string $iv, string $key = '', string $cipher = 'aes-256-ctr', $options = 0)
    {
        $key = '' !== $key ? $key : System::getContainer()->getParameter('secret');

        return openssl_decrypt($encrypted, $cipher, $key, $options, base64_decode($iv, true));
    }
}

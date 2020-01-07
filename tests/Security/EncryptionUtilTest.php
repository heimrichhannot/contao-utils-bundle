<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Security;

use Contao\Config;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Security\EncryptionUtil;

class EncryptionUtilTest extends ContaoTestCase
{
    public function setUp()
    {
        parent::setUp();

        $container = $this->mockContainer();
        $container->setParameter('secret', Config::class);
        System::setContainer($container);
    }

    public function testInstantiation()
    {
        $encrypt = new EncryptionUtil($this->mockContaoFramework());
        $this->assertInstanceOf(EncryptionUtil::class, $encrypt);
    }

    public function testEncrypt()
    {
        $encrypt = new EncryptionUtil($this->mockContaoFramework());
        $result = $encrypt->encrypt('plain', 'key', 'cypher');
        $this->assertFalse($result);

        $result = $encrypt->encrypt('plain');
        $this->assertSame(openssl_encrypt('plain', 'aes-256-ctr', System::getContainer()->getParameter('secret'), 0, base64_decode($result[1], true)), $result[0]);
        $this->assertSame('plain', $encrypt->decrypt($result[0], $result[1]));
    }
}

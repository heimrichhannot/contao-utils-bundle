<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\UtilsBundle\Tests\File;


use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\File\FileStorage;

class FileStorageTest extends ContaoTestCase
{
    public function testGet()
    {
        $tempFolder = $this->getTempDir().'/filestorage';
        $instance = new FileStorage($tempFolder);
        file_put_contents($tempFolder.'/test', 'hallo');
        $this->assertSame($tempFolder.'/test', $instance->get('hallo'));

    }
}
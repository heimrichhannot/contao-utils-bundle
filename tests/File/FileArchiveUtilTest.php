<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\File;

use Contao\FilesModel;
use Contao\TestCase\ContaoTestCase;
use Contao\ZipReader;
use Contao\ZipWriter;
use HeimrichHannot\UtilsBundle\File\FileArchiveUtil;
use HeimrichHannot\UtilsBundle\File\FolderUtil;
use HeimrichHannot\UtilsBundle\Tests\ModelMockTrait;
use HeimrichHannot\UtilsBundle\Tests\ResetContaoSingletonTrait;
use Symfony\Component\Filesystem\Filesystem;

class FileArchiveUtilTest extends ContaoTestCase
{
    use ModelMockTrait;
    use ResetContaoSingletonTrait;

    public function testCreateFileArchive()
    {
        $tmpFolder = $this->getTempDir();

        if (!\defined('TL_ROOT')) {
            \define('TL_ROOT', $this->getTempDir());
        }

        $container = $this->mockContainer();
        $container->setParameter('kernel.project_dir', $tmpFolder);
        $this->resetFilesInstance($container);

        $folderUtilMock = $this->createMock(FolderUtil::class);
        $folderUtilMock->method('createPublicFolder');

        $fileArchivUtil = new FileArchiveUtil($tmpFolder, ['tmp_folder' => 'utils-bundle'], $folderUtilMock);

        $filesystem = new Filesystem();
        $filesystem->dumpFile($tmpFolder.'/hello.txt', 'hello world');
        $filesystem->dumpFile($tmpFolder.'/hello.jpg', 'hello world');
        $filesystem->mkdir($tmpFolder.'/'.ZipWriter::TEMPORARY_FOLDER);

        $files = [];
        $files[] = $this->mockModelObject(FilesModel::class, ['path' => 'hello.txt', 'name' => 'hello.txt']);
        $files[] = $this->mockModelObject(FilesModel::class, ['path' => 'hello.jpg', 'name' => 'hello.jpg']);

        $archivePath = $fileArchivUtil->createFileArchive($files, 'test_archive');

        $this->assertStringStartsWith('utils-bundle/file_archive_util/test_archive', $archivePath);
        $this->assertStringEndsWith('.zip', $archivePath);

        $zipReader = new ZipReader($archivePath);
        $this->assertCount(2, $zipReader->getFileList());
        $this->assertTrue($zipReader->getFile('hello.txt'));
        $this->assertTrue($zipReader->getFile('hello.jpg'));
    }
}

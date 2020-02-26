<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\File;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\DataContainer;
use Contao\File;
use Contao\FilesModel;
use Contao\Folder;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Arrays\ArrayUtil;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\String\StringUtil;
use HeimrichHannot\UtilsBundle\Tests\ResetContaoSingletonTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Config\FileLocator;

class FileUtilTest extends ContaoTestCase
{
    use ResetContaoSingletonTrait;

    public function setUp()
    {
        parent::setUp();

        $fs = new Filesystem();
        $fs->mkdir($this->getTempDir().'/files/');

        if (!\function_exists('standardize')) {
            include_once __DIR__.'/../../vendor/contao/core-bundle/src/Resources/contao/helper/functions.php';
        }
    }

    public function testGetFileList()
    {
        $fileUtil = new FileUtil($this->getContainerMock());
        file_put_contents($this->getTempDir().'/files/testfile1', 'test');

        $fileList = $fileUtil->getFileList($this->getTempDir().'/files', __DIR__, 'protectBaseUrl');
        $this->assertSame('protectBaseUrl?file='.__DIR__.'/testfile1', $fileList[0]['absUrl']);

        file_put_contents($this->getTempDir().'/files/testfile2', 'test');
        file_put_contents($this->getTempDir().'/files/testfile3', 'test');

        $fileList = $fileUtil->getFileList($this->getTempDir().'/files', __DIR__);

        $this->assertCount(3, $fileList);
        $this->assertArrayHasKey(0, $fileList);
        $this->assertArrayHasKey('filename', $fileList[0]);
        $this->assertNotSame('', $fileList[0]['filename']);
        $this->assertArrayHasKey(1, $fileList);
        $this->assertArrayHasKey('filename', $fileList[1]);
        $this->assertNotSame('', $fileList[1]['filename']);
        $this->assertArrayHasKey(2, $fileList);
        $this->assertArrayHasKey('filename', $fileList[2]);
        $this->assertNotSame('', $fileList[2]['filename']);

        $fileList = $fileUtil->getFileList($this->getTempDir().'/fileList', __DIR__);

        $this->assertCount(0, $fileList);
    }

    public function testGetUniqueFileNameWithinTarget()
    {
        $container = $this->getContainerMock();
        $this->resetFilesInstance($container);
        System::setContainer($container); // Need for contao core file class
        $fileUtil = new FileUtil($container);
        $projectDir = $container->getParameter('kernel.project_dir');

        $fileName = $fileUtil->getUniqueFileNameWithinTarget('/files/test', 'te');
        $this->assertSame('files/_1.', $fileName);

        $fileName = $fileUtil->getUniqueFileNameWithinTarget($projectDir.'/test/test/test');
        $this->assertFalse($fileName);

        file_put_contents($projectDir.'/files/test', 'test');
        $fileName = $fileUtil->getUniqueFileNameWithinTarget($projectDir.'/files/test');
        $this->assertSame('files/test_1.', $fileName);

        file_put_contents($projectDir.'/files/test_10', 'test');
        $fileName = $fileUtil->getUniqueFileNameWithinTarget($projectDir.'/files/test_10', null, 10);
        $this->assertNotSame('files/test', $fileName);

        $fileName = $fileUtil->getUniqueFileNameWithinTarget($projectDir.'/files/test', null, 100);
        $this->assertNotSame('files/test', $fileName);
    }

    public function testFormatSizeUnits()
    {
        $fileUtil = new FileUtil($this->getContainerMock());

        $bytes = $fileUtil->formatSizeUnits(1073741824);
        $this->assertSame('1.00 GB', $bytes);
        $bytes = $fileUtil->formatSizeUnits(1048576);
        $this->assertSame('1.00 MB', $bytes);
        $bytes = $fileUtil->formatSizeUnits(1024);
        $this->assertSame('1.00 KB', $bytes);
        $bytes = $fileUtil->formatSizeUnits(3);
        $this->assertSame('3 Bytes', $bytes);
        $bytes = $fileUtil->formatSizeUnits(1);
        $this->assertSame('1 Byte', $bytes);
        $bytes = $fileUtil->formatSizeUnits(10737.41824);
        $this->assertSame('10.49 KB', $bytes);

        try {
            $bytes = $fileUtil->formatSizeUnits('107374,1824');
        } catch (\Exception $exception) {
            $this->assertSame('A non well formed numeric value encountered', $exception->getMessage());
        }
        $bytes = $fileUtil->formatSizeUnits(1073741894, true);
        $this->assertSame('1.00&nbsp;GB', $bytes);
        $bytes = $fileUtil->formatSizeUnits(0.1073741894, true);
        $this->assertSame('0&nbsp;Bytes', $bytes);
    }

    public function testGetPathWithoutFilename()
    {
        $fileUtil = new FileUtil($this->getContainerMock());
        $path = $fileUtil->getPathWithoutFilename($this->getTempDir().'/file/testfile1');
        $this->assertSame($this->getTempDir().'/file', $path);

        $path = $fileUtil->getPathWithoutFilename('');
        $this->assertSame('', $path);

        $path = $fileUtil->getPathWithoutFilename(1234);
        $this->assertSame('.', $path);
    }

    public function testGetFileExtension()
    {
        $fileUtil = new FileUtil($this->getContainerMock());
        $fileExtension = $fileUtil->getFileExtension($this->getTempDir().'/file/testfile1');
        $this->assertSame('', $fileExtension);
        $fileExtension = $fileUtil->getFileExtension($this->getTempDir().'/file/testfile1.txt');
        $this->assertSame('txt', $fileExtension);
        $fileExtension = $fileUtil->getFileExtension($this->getTempDir().'/file/testfile1.xml');
        $this->assertSame('xml', $fileExtension);
        $fileExtension = $fileUtil->getFileExtension($this->getTempDir().'/file/testfile1...xml');
        $this->assertSame('xml', $fileExtension);
        $fileExtension = $fileUtil->getFileExtension($this->getTempDir());
        $this->assertSame('', $fileExtension);
        $fileExtension = $fileUtil->getFileExtension($this->getTempDir().'.xml');
        $this->assertSame('xml', $fileExtension);
        $fileExtension = $fileUtil->getFileExtension('');
        $this->assertSame('', $fileExtension);
        $fileExtension = $fileUtil->getFileExtension(1234);
        $this->assertSame('', $fileExtension);
    }

    public function testAddUniqueIdToFilename()
    {
        $container = $this->getContainerMock();
        System::setContainer($container);
        $fileUtil = new FileUtil($container);

        $file = $fileUtil->addUniqueIdToFilename('testFile');
        $this->assertNotSame('testFile', $file);
    }

    public function testSanitizeFileName()
    {
        $fileUtil = new FileUtil($this->getContainerMock());

        $fileName = $fileUtil->sanitizeFileName('fileName');
        $this->assertSame('filename', $fileName);

        $fileName = $fileUtil->sanitizeFileName('fileName', 3);
        $this->assertSame('fi', $fileName);

        $fileName = $fileUtil->sanitizeFileName('საბეჭდი_მანქანა');
        $this->assertSame('_', $fileName);
    }

    public function testGetFilesFromUuid()
    {
        $filesModel = $this->mockClassWithProperties(FilesModel::class, ['path' => 'files']);
        $filesAdapter = $this->mockAdapter(['findByUuid']);
        $filesAdapter->method('findByUuid')->willReturn($filesModel);
        $framework = $this->mockContaoFramework([FilesModel::class => $filesAdapter]);

        $fileUtil = new FileUtil($this->getContainerMock(null, $framework));
        $file = $fileUtil->getFileFromUuid('uuid');
        $this->assertNull($file);

        file_put_contents($this->getTempDir().'/files/testFile', 'test');
        $filesModel = $this->mockClassWithProperties(FilesModel::class, ['path' => 'files/testFile']);
        $filesAdapter = $this->mockAdapter(['findByUuid']);
        $filesAdapter->method('findByUuid')->willReturn($filesModel);
        $framework = $this->mockContaoFramework([FilesModel::class => $filesAdapter]);

        $fileUtil = new FileUtil($this->getContainerMock(null, $framework));
        $file = $fileUtil->getFileFromUuid('uuid');
        $this->assertInstanceOf(File::class, $file);

        $filesAdapter = $this->mockAdapter(['findByUuid']);
        $filesAdapter->method('findByUuid')->willReturn(null);
        $framework = $this->mockContaoFramework([FilesModel::class => $filesAdapter]);

        $fileUtil = new FileUtil($this->getContainerMock(null, $framework));
        $file = $fileUtil->getFileFromUuid('uuid');
        $this->assertNull($file);
    }

    public function testGetPathFromUuid()
    {
        $filesModel = $this->mockClassWithProperties(FilesModel::class, ['path' => 'files/testfile1']);
        $filesAdapter = $this->mockAdapter(['findByUuid']);
        $filesAdapter->method('findByUuid')->willReturn($filesModel);
        $framework = $this->mockContaoFramework([FilesModel::class => $filesAdapter]);

        $fileUtil = new FileUtil($this->getContainerMock(null, $framework));
        $path = $fileUtil->getPathFromUuid($this->getTempDir().'/files', false);
        $this->assertSame('files/testfile1', $path);

        $path = $fileUtil->getPathFromUuid($this->getTempDir().'/files');
        $this->assertSame('files/testfile1', $path);

        $filesAdapter = $this->mockAdapter(['findByUuid']);
        $filesAdapter->method('findByUuid')->willReturn(null);
        $framework = $this->mockContaoFramework([FilesModel::class => $filesAdapter]);

        $fileUtil = new FileUtil($this->getContainerMock(null, $framework));
        $path = $fileUtil->getPathFromUuid($this->getTempDir().'files');
        $this->assertNull($path);
    }

    public function testGetFolderFromUuid()
    {
        $filesModel = $this->mockClassWithProperties(FilesModel::class, ['path' => 'files']);
        $filesAdapter = $this->mockAdapter(['findByUuid']);
        $filesAdapter->method('findByUuid')->willReturn($filesModel);
        $framework = $this->mockContaoFramework([FilesModel::class => $filesAdapter]);

        $fileUtil = new FileUtil($this->getContainerMock(null, $framework));

        $path = $fileUtil->getFolderFromUuid('uuid');
        $this->assertInstanceOf(Folder::class, $path);

        $filesAdapter = $this->mockAdapter(['findByUuid']);
        $filesAdapter->method('findByUuid')->willReturn(null);
        $framework = $this->mockContaoFramework([FilesModel::class => $filesAdapter]);

        $fileUtil = new FileUtil($this->getContainerMock(null, $framework));
        $path = $fileUtil->getFolderFromUuid('uuid');
        $this->assertFalse($path);
    }

    public function testGetFileLineCount()
    {
        file_put_contents($this->getTempDir().'/files/testFile', 'test');

        $container = $this->getContainerMock();
        $fileUtil = new FileUtil($container);

        $lines = $fileUtil->getFileLineCount($this->getTempDir().'/files/testFile');
        $this->assertSame(1, $lines);

        $lines = $fileUtil->getFileLineCount('/foo');
        $this->assertTrue(false !== strpos($lines, 'fopen('.$container->getParameter('kernel.project_dir').'/foo): failed to open stream:'));
    }

    public function testGetFolderFromDca()
    {
        $filesModel = $this->mockClassWithProperties(FilesModel::class, ['path' => 'files']);
        $filesAdapter = $this->mockAdapter(['findByUuid']);
        $filesAdapter->method('findByUuid')->willReturn($filesModel);
        $framework = $this->mockContaoFramework([FilesModel::class => $filesAdapter]);

        $fileUtil = new FileUtil($this->getContainerMock(null, $framework));
        $folder = $fileUtil->getFolderFromDca($this->getTempDir().'files');
        $this->assertSame($this->getTempDir().'files', $folder);

        $folder = $fileUtil->getFolderFromDca('3712c116-1193-11e8-b642-0ed5f89f718b');
        $this->assertSame('files', $folder);

        $file = new File('files/dcaFile');
        $folder = $fileUtil->getFolderFromDca($file);
        $this->assertSame('files/dcaFile', $folder);

        $file = $this->mockClassWithProperties(FilesModel::class, ['path' => 'files/dcaFile']);
        $folder = $fileUtil->getFolderFromDca($file);
        $this->assertSame('files/dcaFile', $folder);

        $folder = $fileUtil->getFolderFromDca(
            function ($dca) {
                return 'files/dcaFile';
            },
            $this->getDataContainerMock()
        );
        $this->assertSame('files/dcaFile', $folder);

        $folder = $fileUtil->getFolderFromDca([self::class, 'getFolder'], $this->getDataContainerMock());
        $this->assertSame('files', $folder);

        try {
            $fileUtil->getFolderFromDca('dlfjn../ds');
        } catch (\Exception $exception) {
            $this->assertSame('Invalid target path dlfjn../ds', $exception->getMessage());
        }
    }

    /**
     * @return DataContainer|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getDataContainerMock($properties = true)
    {
        if ($properties) {
            return $this->mockClassWithProperties(DataContainer::class, ['id' => 1, 'table' => 'testTable']);
        }

        return $this->createMock(DataContainer::class);
    }

    public function getFolder($dca)
    {
        return 'files';
    }

    /**
     * @param ContaoFramework $framework
     *
     * @return ContainerBuilder|ContainerInterface
     */
    protected function getContainerMock(ContainerBuilder $container = null, $framework = null)
    {
        if (!$container) {
            $container = $this->mockContainer($this->getTempDir());
        }

        if (!$framework) {
            $filesModel = $this->mockClassWithProperties(FilesModel::class, ['path' => $this->getTempDir().'files']);
            $filesAdapter = $this->mockAdapter(['findByUuid']);
            $filesAdapter->method('findByUuid')->willReturn($filesModel);
            $framework = $this->mockContaoFramework([FilesModel::class => $filesAdapter]);
        }
        $container->set('contao.framework', $framework);
        $container->setParameter('contao.resources_paths', [__DIR__.'/../vendor/contao/core-bundle/src/Resources/contao']);

        $utilsString = new StringUtil($this->mockContaoFramework());
        $container->set('huh.utils.string', $utilsString);

        /** @noinspection PhpParamsInspection */
        $containerUtils = new ContainerUtil($container, $this->createMock(FileLocator::class), $this->createMock(ScopeMatcher::class));
        $container->set('huh.utils.container', $containerUtils);

        $arrayUtils = new ArrayUtil($container);
        $container->set('huh.utils.array', $arrayUtils);

        System::setContainer($container);

        return $container;
    }
}

<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\File;

use Contao\CoreBundle\Command\SymlinksCommand;
use Contao\CoreBundle\Config\ResourceFinderInterface;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\File\FolderUtil;
use HeimrichHannot\UtilsBundle\Tests\ResetContaoSingletonTrait;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class FolderUtilTest extends ContaoTestCase
{
    use ResetContaoSingletonTrait;

    /**
     * @throws \ReflectionException
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCreatePublicFolder()
    {
        $tmpFolder = $this->getTempDir();

        if (!\defined('TL_ROOT')) {
            \define('TL_ROOT', $tmpFolder);
        }
        $filesystem = new Filesystem();
        $container = $this->mockContainer($tmpFolder);
        $container->setParameter('kernel.project_dir', $tmpFolder);
        $container->set('filesystem', $filesystem);
        $this->resetFilesInstance($container);
        $resourceFinder = $this->createMock(ResourceFinderInterface::class);

        $kernel = $this->createMock(KernelInterface::class);
        $symlinkCommand = $this->getMockBuilder(SymlinksCommand::class)->setConstructorArgs([$tmpFolder, $tmpFolder, $tmpFolder, $resourceFinder])->setMethods(['execute'])->getMock();
        $symlinkCommand->method('execute')->willReturn(0);
        $folderUtil = new FolderUtil($tmpFolder.'/web', $kernel, $symlinkCommand);

        $folder = 'should_be_public';

        $filesystem->mkdir($tmpFolder.'/'.$folder);
        $filesystem->mkdir($tmpFolder.'/web');
        $filesystem->mkdir($tmpFolder.'/system/tmp');
        $folderUtil->createPublicFolder($folder);

        $this->assertTrue($filesystem->exists($tmpFolder.'/'.$folder.'/.public'));

        $symlinkCommand = $this->getMockBuilder(SymlinksCommand::class)->setConstructorArgs([$tmpFolder, $tmpFolder, $tmpFolder, $resourceFinder])->setMethods(['execute'])->getMock();
        $symlinkCommand->method('execute')->willReturn(1);
        $folderUtil = new FolderUtil($tmpFolder.'/web', $kernel, $symlinkCommand);
        $this->expectExceptionMessage('The symlink command exited with errors.');
        $folderUtil->createPublicFolder($folder);
    }
}

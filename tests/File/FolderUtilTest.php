<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\UtilsBundle\Tests\File;


use Contao\CoreBundle\Command\SymlinksCommand;
use Contao\CoreBundle\Config\ResourceFinderInterface;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\File\FolderUtil;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class FolderUtilTest extends ContaoTestCase
{
    protected function setUp()
    {
        parent::setUp();
        if (!\defined('TL_ROOT')) {
            \define('TL_ROOT', $this->getTempDir());
        }
    }


    public function testCreatePublicFolder()
    {
        $tmpFolder = $this->getTempDir();
        $filesystem = new Filesystem();
        $container = $this->mockContainer();
        $container->setParameter('kernel.project_dir', $tmpFolder);
        $container->set('filesystem', $filesystem);
        System::setContainer($container);
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
        $this->expectExceptionMessage("The symlink command exited with errors.");
        $folderUtil->createPublicFolder($folder);
    }
}
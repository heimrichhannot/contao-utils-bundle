<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\UtilsBundle\File;


use Contao\CoreBundle\Command\SymlinksCommand;
use Contao\Folder;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class FolderUtil
{
    /**
     * @var string
     */
    private $webDir;
    /**
     * @var KernelInterface
     */
    private $kernel;
    /**
     * @var SymlinksCommand
     */
    private $symlinksCommand;


    /**
     * FolderUtil constructor.
     * @param string $webDir
     * @param KernelInterface $kernel
     */
    public function __construct(string $webDir, KernelInterface $kernel, SymlinksCommand $symlinksCommand)
    {
        $this->webDir = $webDir;
        $this->kernel = $kernel;
        $this->symlinksCommand = $symlinksCommand;
    }



    /**
     * Creates an symlink to the given folder in the web director, if not already exist.
     *
     * @param string $folderPath
     *
     * @throws \Exception
     */
    public function createPublicFolder(string $folderPath): void
    {
        if (!is_dir($this->webDir.DIRECTORY_SEPARATOR.$folderPath))
        {
            $folder = new Folder($folderPath);
            $folder->unprotect();

            $application = new Application();
            $application->add($this->symlinksCommand);
            $application->setAutoExit(false);
            $input = new ArrayInput([
                'command' => 'contao:symlinks',
            ]);
            $output = new NullOutput();
            $result = $application->run($input, $output);
            if ($result > 0) {
                throw new \Exception("The symlink command exited with errors.");
            }
        }
    }
}
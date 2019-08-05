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


use Contao\FilesModel;
use Contao\ZipWriter;
use Symfony\Component\Filesystem\Filesystem;

class FileArchiveUtil
{
    /**
     * @var string
     */
    private $projectDir;
    /**
     * @var array
     */
    private $utilsConfig;
    /**
     * @var FolderUtil
     */
    private $folderUtil;

    /**
     * FileArchiveUtil constructor.
     * @param string $projectDir
     * @param array $utilsConfig
     * @param FolderUtil $folderUtil
     */
    public function __construct(string $projectDir, array $utilsConfig, FolderUtil $folderUtil)
    {
        $this->projectDir = $projectDir;
        $this->utilsConfig = $utilsConfig;
        $this->folderUtil = $folderUtil;
    }

    /**
     * Create a temporary zip file and return the file path
     *
     * @param FilesModel[]|array $items
     * @param string $archiveName
     * @return string The path to the temporary zip file.
     * @throws \Exception
     */
    public function createFileArchive(array $items, string $archiveName)
    {
        $filesystem = new Filesystem();
        $tmpFolder = $this->utilsConfig['tmp_folder'].DIRECTORY_SEPARATOR.'file_archive_util';
        $absoluteTmpFolder = $this->projectDir.DIRECTORY_SEPARATOR.$tmpFolder;
        if (!$filesystem->exists($absoluteTmpFolder)) {
            $filesystem->mkdir($absoluteTmpFolder);
        }

        $unique = false;
        while(!$unique) {
            $fileName = uniqid($archiveName.'_'.date('Ymd').'_').'.zip';
            $unique = !$filesystem->exists($absoluteTmpFolder.'/'.$fileName);
        }
        $filePath = $tmpFolder.DIRECTORY_SEPARATOR.$fileName;

        $this->folderUtil->createPublicFolder($tmpFolder);

        $zipWriter = new ZipWriter($filePath);

        foreach ($items as $item) {
            $zipWriter->addFile($item->path, $item->name);
        }

        $zipWriter->close();
        return $filePath;
    }
}
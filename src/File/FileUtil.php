<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\UtilsBundle\File;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\File;
use Contao\FilesModel;
use Contao\Folder;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;

class FileUtil
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Get a unique filename within given target folder, remove uniqid() suffix from file (optional, add $prefix) and append file count by name to
     * file if file with same name already exists in target folder.
     *
     * @param string $target The target file path
     * @param string $prefix A uniqid prefix from the given target file, that was added to the file before and should be removed again
     * @param        $i      integer Internal counter for recursion usage or if you want to add the number to the file
     *
     * @return string | false The filename with the target folder and unique id or false if something went wrong (e.g. target does not exist)
     */
    public function getUniqueFileNameWithinTarget($target, $prefix = null, $i = 0)
    {
        $objFile = new \File($target, true);

        $target = ltrim(str_replace(TL_ROOT, '', $target), '/');
        $strPath = str_replace('.'.$objFile->extension, '', $target);

        if ($prefix && false !== ($pos = strpos($strPath, $prefix))) {
            $strPath = str_replace(substr($strPath, $pos, strlen($strPath)), '', $strPath);
            $target = $strPath.'.'.$objFile->extension;
        }

        // Create the parent folder
        if (!file_exists($objFile->dirname)) {
            $objFolder = new \Folder(ltrim(str_replace(TL_ROOT, '', $objFile->dirname), '/'));

            // something went wrong with folder creation
            if (null === $objFolder->getModel()) {
                return false;
            }
        }

        if (file_exists(TL_ROOT.'/'.$target)) {
            // remove suffix
            if ($i > 0 && StringUtil::endsWith($strPath, '_'.$i)) {
                $strPath = rtrim($strPath, '_'.$i);
            }

            // increment counter & add extension again
            ++$i;

            // for performance reasons, add new unique id to path to make recursion come to end after 100 iterations
            if ($i > 100) {
                return static::getUniqueFileNameWithinTarget(static::addUniqIdToFilename($strPath.'.'.$objFile->extension, null, false));
            }

            return static::getUniqueFileNameWithinTarget($strPath.'_'.$i.'.'.$objFile->extension, $prefix, $i);
        }

        return $target;
    }

    /**
     * Returns the file list for a given directory.
     *
     * @param string $strDir           - the absolute local path to the directory (e.g. /dir/mydir)
     * @param string $baseUrl          - the relative uri (e.g. /tl_files/mydir)
     * @param string $protectedBaseUrl - domain + request uri -> absUrl will be domain + request uri + ?file=$baseUrl/filename.ext
     *
     * @return array file list containing file objects
     */
    public function getFileList($strDir, $baseUrl, $protectedBaseUrl = null)
    {
        $arrResult = [];
        if (is_dir($strDir)) {
            if ($handler = opendir($strDir)) {
                while (false !== ($strFile = readdir($handler))) {
                    if ('.' == substr($strFile, 0, 1)) {
                        continue;
                    }
                    $arrFile = [];
                    $arrFile['filename'] = htmlentities($strFile);
                    if ($protectedBaseUrl) {
                        $arrFile['absUrl'] = $protectedBaseUrl.(empty($_GET) ? '?' : '&').'file='.urlencode($arrFile['absUrl']);
                    } else {
                        $arrFile['absUrl'] = str_replace('\\', '/', str_replace('//', '', $baseUrl.'/'.$strFile));
                    }
                    $arrFile['path'] = str_replace($arrFile['filename'], '', $arrFile['absUrl']);
                    $arrFile['filesize'] =
                        self::formatSizeUnits(filesize(str_replace('\\', '/', str_replace('//', '', $strDir.'/'.$strFile))), true);

                    $arrResult[] = $arrFile;
                }
                closedir($handler);
            }
        }
        Arrays::aasort($arrResult, 'filename');

        return $arrResult;
    }

    public function formatSizeUnits($bytes, $keepTogether = false)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2).($keepTogether ? '&nbsp;' : ' ').'GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2).($keepTogether ? '&nbsp;' : ' ').'MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2).($keepTogether ? '&nbsp;' : ' ').'KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes.($keepTogether ? '&nbsp;' : ' ').'Bytes';
        } elseif (1 == $bytes) {
            $bytes = $bytes.($keepTogether ? '&nbsp;' : ' ').'Byte';
        } else {
            $bytes = '0'.($keepTogether ? '&nbsp;' : ' ').'Bytes';
        }

        return $bytes;
    }

    public function getPathWithoutFilename($strPathToFile)
    {
        $path = pathinfo($strPathToFile);

        return $path['dirname'];
    }

    public function getFileExtension($strPath)
    {
        return pathinfo($strPath, PATHINFO_EXTENSION);
    }

    /**
     * @param      $varUuid
     * @param bool $blnCheckExists
     *
     * @return null|string Return the path of the file, or null if not exists
     */
    public function getPathFromUuid($varUuid, $blnCheckExists = true)
    {
        if (null !== ($objFile = \FilesModel::findByUuid($varUuid))) {
            if (!$blnCheckExists) {
                return $objFile->path;
            }

            if (file_exists(TL_ROOT.'/'.$objFile->path)) {
                return $objFile->path;
            }
        }

        return null;
    }

    /**
     * @param      $varUuid
     * @param bool $blnDoNotCreate
     *
     * @return \File|null Return the file object
     */
    public function getFileFromUuid($varUuid, $blnDoNotCreate = true)
    {
        if ($strPath = static::getPathFromUuid($varUuid)) {
            if (is_dir(TL_ROOT.DIRECTORY_SEPARATOR.$strPath)) {
                return null;
            }

            return new \File($strPath, $blnDoNotCreate);
        }
    }

    /**
     * @param      $varUuid
     * @param bool $blnDoNotCreate
     *
     * @return bool|Folder Return the folder object
     */
    public function getFolderFromUuid($varUuid, $blnDoNotCreate = true)
    {
        if ($path = static::getPathFromUuid($varUuid)) {
            return new Folder($path, $blnDoNotCreate);
        }

        return false;
    }

    /**
     * Add a unique identifier to a file name.
     *
     * @param string $fileName    The file name, can be with or without path
     * @param string $prefix      add a prefix to the unique identifier, with an empty prefix, the returned string will be 13 characters long
     * @param bool   $moreEntropy if set to TRUE, will add additional entropy (using the combined linear congruential generator) at the end of the
     *                            return value, which increases the likelihood that the result will be unique
     *
     * @return string Filename with timestamp based unique identifier
     */
    public function addUniqueIdToFilename($fileName, $prefix = null, $moreEntropy = true)
    {
        $file = new File($fileName, true);

        $directory = ltrim(str_replace(TL_ROOT, '', $file->dirname), '/');

        return ($directory ? $directory.'/' : '').$file->filename.uniqid($prefix, $moreEntropy).
               ($file->extension ? '.'.$file->extension : '');
    }

    /**
     * Sanitize filename and removes "id-" prefix generated by contao standardize method.
     *
     * @param string $fileName          The file name, can be with or without path
     * @param int    $maxCount          Max filename length
     * @param bool   $preserveUppercase Set to true if you want to lower case the file name
     *
     * @return string The sanitized filename
     */
    public function sanitizeFileName($fileName, $maxCount = 0, $preserveUppercase = false)
    {
        $file = new \File($fileName, true);

        $name = $file->filename;

        $name = standardize($name, $preserveUppercase);

        if ('id-' != $name && !System::getContainer()->get('huh.utils.string')->startsWith($fileName, 'id-')) {
            $name = preg_replace('/^(id-)/', '', $name);
        }

        if ($maxCount > 0) {
            $name = substr($name, 0, $maxCount - 1);
        }

        $directory = ltrim(str_replace(TL_ROOT, '', $file->dirname), '/');

        return ($directory ? $directory.'/' : '').$name.($file->extension ? ('.'.strtolower($file->extension)) : '');
    }

    public function sendTextAsFileToBrowser($content, $fileName)
    {
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
        header('Content-Type: text/plain');
        header('Connection: close');
        echo $content;
        die();
    }

    /**
     * Get real folder from datacontainer attribute.
     *
     * @param mixed              $folder The folder as uuid, function, callback array('CLASS', 'method') or string (files/...)
     * @param DataContainer|null $dc     Optional \DataContainer, required for function and callback
     *
     * @throws \Exception If ../ is part of the path
     *
     * @return mixed|null The folder path or null
     */
    public function getFolderFromDca($folder, DataContainer $dc = null, $doNotCreate = true)
    {
        // upload folder
        if (is_array($folder) && null !== $dc) {
            $callback = $folder;
            $folder = System::importStatic($callback[0])->{$callback[1]}($dc);
        } elseif (is_callable($folder) && null !== $dc) {
            $method = $folder;
            $folder = $method($dc);
        } else {
            if (false !== strpos($folder, '../')) {
                throw new \Exception("Invalid target path $folder");
            }
        }

        if ($folder instanceof File) {
            $folder = $folder->value;
        } else {
            if ($folder instanceof FilesModel) {
                $folder = $folder->path;
            }
        }

        if (Validator::isUuid($folder)) {
            $folderObj = $this->getFolderFromUuid($folder, $doNotCreate);
            $folder = $folderObj->value;
        }

        return $folder;
    }

    public function getFileLineCount($file)
    {
        $count = 0;
        $handle = fopen(TL_ROOT.'/'.ltrim(str_replace(TL_ROOT, '', $file), '/'), 'r');

        while (!feof($handle)) {
            $line = fgets($handle);
            ++$count;
        }

        fclose($handle);

        return $count;
    }
}

<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
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
        $file = new File($target);

        $target = ltrim(str_replace(TL_ROOT, '', $target), '/');
        $path = str_replace('.'.$file->extension, '', $target);

        if ($prefix && false !== ($pos = strpos($path, $prefix))) {
            $path = str_replace(substr($path, $pos, strlen($path)), '', $path);
            $target = $path.'.'.$file->extension;
        }

        // Create the parent folder
        if (!file_exists($file->dirname)) {
            $folder = new Folder(ltrim(str_replace(TL_ROOT, '', $file->dirname), '/'));

            // something went wrong with folder creation
            if (null === $folder->getModel()) {
                return false;
            }
        }

        if (file_exists(TL_ROOT.'/'.$target)) {
            // remove suffix
            if ($i > 0 && System::getContainer()->get('huh.utils.string')->endsWith($path, '_'.$i)) {
                $path = rtrim($path, '_'.$i);
            }

            // increment counter & add extension again
            ++$i;

            // for performance reasons, add new unique id to path to make recursion come to end after 100 iterations
            if ($i > 100) {
                return $this->getUniqueFileNameWithinTarget($this->addUniqueIdToFilename($path.'.'.$file->extension, null, false));
            }

            return $this->getUniqueFileNameWithinTarget($path.'_'.$i.'.'.$file->extension, $prefix, $i);
        }

        return $target;
    }

    /**
     * Returns the file list for a given directory.
     *
     * @param string $dir              - the absolute local path to the directory (e.g. /dir/mydir)
     * @param string $baseUrl          - the relative uri (e.g. /tl_files/mydir)
     * @param string $protectedBaseUrl - domain + request uri -> absUrl will be domain + request uri + ?file=$baseUrl/filename.ext
     *
     * @return array file list containing file objects
     */
    public function getFileList($dir, $baseUrl, $protectedBaseUrl = null)
    {
        $results = [];

        if (is_dir($dir)) {
            if ($handler = opendir($dir)) {
                while (false !== ($file = readdir($handler))) {
                    if ('.' == substr($file, 0, 1)) {
                        continue;
                    }

                    $fileArray = [];
                    $fileArray['filename'] = htmlentities($file);

                    if ($protectedBaseUrl) {
                        $fileArray['absUrl'] = $protectedBaseUrl.(empty($_GET) ? '?' : '&').'file='.str_replace('//', '', $baseUrl.'/'.$file);
                    } else {
                        $fileArray['absUrl'] = str_replace('\\', '/', str_replace('//', '', $baseUrl.'/'.$file));
                    }

                    $fileArray['path'] = str_replace($fileArray['filename'], '', $fileArray['absUrl']);
                    $fileArray['filesize'] = $this->formatSizeUnits(filesize(str_replace('\\', '/', str_replace('//', '', $dir.'/'.$file))), true);

                    $results[] = $fileArray;
                }

                closedir($handler);
            }
        }

        System::getContainer()->get('huh.utils.array')->aasort($results, 'filename');

        return $results;
    }

    public function formatSizeUnits(int $bytes, $keepTogether = false)
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

    public function getPathWithoutFilename($pathToFile)
    {
        $path = pathinfo($pathToFile);

        if (!isset($path['dirname'])) {
            return '';
        }

        return $path['dirname'];
    }

    public function getFileExtension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * @param      $uuid
     * @param bool $checkIfExists
     *
     * @return null|string Return the path of the file, or null if not exists
     */
    public function getPathFromUuid($uuid, $checkIfExists = true)
    {
        if (null !== ($file = $this->framework->getAdapter(FilesModel::class)->findByUuid($uuid))) {
            if (!$checkIfExists) {
                return $file->path;
            }

            if (file_exists(TL_ROOT.'/'.$file->path)) {
                return $file->path;
            }
        }

        return null;
    }

    /**
     * @param      $uuid
     * @param bool $doNotCreate
     *
     * @return File|null Return the file object
     */
    public function getFileFromUuid($uuid)
    {
        if ($path = $this->getPathFromUuid($uuid)) {
            if (is_dir(TL_ROOT.DIRECTORY_SEPARATOR.$path)) {
                return null;
            }

            return new File($path);
        }
    }

    /**
     * @param      $uuid
     * @param bool $doNotCreate
     *
     * @return bool|Folder Return the folder object
     */
    public function getFolderFromUuid($uuid)
    {
        if ($path = $this->getPathFromUuid($uuid)) {
            return new Folder($path);
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
        $file = new File($fileName);

        $directory = ltrim(str_replace(TL_ROOT, '', $file->dirname), '/');

        return ($directory ? $directory.'/' : '').$file->filename.uniqid($prefix, $moreEntropy).($file->extension ? '.'.$file->extension : '');
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
        $file = new File($fileName);

        $name = $file->filename;

        $name = \Patchwork\Utf8::toAscii(StringUtil::standardize($name, $preserveUppercase), '');

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
    public function getFolderFromDca($folder, DataContainer $dc = null)
    {
        // upload folder
        if (is_array($folder) && null !== $dc) {
            $callback = $folder;
            $folder = System::importStatic($callback[0])->{$callback[1]}($dc);
        } elseif (is_callable($folder) && null !== $dc) {
            $method = $folder;
            $folder = $method($dc);
        } elseif (is_string($folder)) {
            if (false !== strpos($folder, '../')) {
                throw new \Exception("Invalid target path $folder");
            }
        }

        if ($folder instanceof File) {
            $folder = $folder->value;
        } elseif ($folder instanceof FilesModel) {
            $folder = $folder->path;
        }

        if (Validator::isUuid($folder)) {
            $folderObj = $this->getFolderFromUuid($folder);
            $folder = $folderObj->value;
        }

        return $folder;
    }

    /**
     * @param $file
     *
     * @return int|string
     */
    public function getFileLineCount($file)
    {
        $count = 0;
        try {
            $handle = fopen(TL_ROOT.'/'.ltrim(str_replace(TL_ROOT, '', $file), '/'), 'r');
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }

        while (!feof($handle)) {
            $line = fgets($handle);
            ++$count;
        }

        fclose($handle);

        return $count;
    }
}

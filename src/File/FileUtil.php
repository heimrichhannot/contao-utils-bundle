<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\File;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\DataContainer;
use Contao\Dbafs;
use Contao\File;
use Contao\FilesModel;
use Contao\Folder;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use Ghostscript\Transcoder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FileUtil
{
    const FILE_UTIL_CONVERT_FILE_TYPE = 'png';

    /** @var ContaoFramework */
    protected $framework;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->framework = $container->get('contao.framework');
        $this->container = $container;
    }

    /**
     * Get a unique filename within given target folder, remove uniqid() suffix from file (optional, add $prefix) and append file count by name to
     * file if file with same name already exists in target folder.
     *
     * @param string $target The target file path
     * @param string $prefix A uniqid prefix from the given target file, that was added to the file before and should be removed again
     * @param        $i      integer Internal counter for recursion usage or if you want to add the number to the file
     *
     * @throws \Exception
     *
     * @return string | false The filename with the target folder and unique id or false if something went wrong (e.g. target does not exist)
     */
    public function getUniqueFileNameWithinTarget($target, $prefix = null, $i = 0)
    {
        $target = ltrim(str_replace($this->container->getParameter('kernel.project_dir'), '', $target), '/');
        $file = new File($target);

        $path = $target;

        if ($file->extension) {
            $path = str_replace('.'.$file->extension, '', $target);
        }

        if ($prefix && false !== ($pos = strpos($path, $prefix))) {
            $path = str_replace(substr($path, $pos, \strlen($path)), '', $path);
            $target = $path.'.'.$file->extension;
        }

        // Create the parent folder
        if (!file_exists($file->dirname)) {
            $folder = new Folder(ltrim(str_replace($this->container->getParameter('kernel.project_dir'), '', $file->dirname), '/'));

            // something went wrong with folder creation
            if (null === $folder->getModel()) {
                return false;
            }
        }

        if (file_exists($this->container->getParameter('kernel.project_dir').'/'.$target)) {
            // remove suffix
            if ($i > 0 && $this->container->get('huh.utils.string')->endsWith($path, '_'.$i)) {
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
                        $fileArray['absUrl'] =
                            $protectedBaseUrl.(empty($_GET) ? '?' : '&').'file='.str_replace('//', '', $baseUrl.'/'.$file);
                    } else {
                        $fileArray['absUrl'] = str_replace('\\', '/', str_replace('//', '', $baseUrl.'/'.$file));
                    }

                    $fileArray['path'] = str_replace($fileArray['filename'], '', $fileArray['absUrl']);
                    $fileArray['filesize'] =
                        $this->formatSizeUnits(filesize(str_replace('\\', '/', str_replace('//', '', $dir.'/'.$file))), true);

                    $results[] = $fileArray;
                }

                closedir($handler);
            }
        }

        $this->container->get('huh.utils.array')->aasort($results, 'filename');

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
        if (is_dir($path)) {
            return '';
        }

        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * @param      $uuid
     * @param bool $checkIfExists
     *
     * @return string|null Return the path of the file, or null if not exists
     */
    public function getPathFromUuid($uuid, $checkIfExists = true)
    {
        if (null !== ($file = $this->framework->getAdapter(FilesModel::class)->findByUuid($uuid))) {
            if (!$checkIfExists) {
                return $file->path;
            }

            if (file_exists($this->container->getParameter('kernel.project_dir').'/'.$file->path)) {
                return $file->path;
            }
        }

        return null;
    }

    public function getFileContentFromUuid($uuid)
    {
        $file = $this->getFileFromUuid($uuid);

        if (!$file || !$file->exists()) {
            return null;
        }

        return file_get_contents(System::getContainer()->get('huh.utils.container')->getProjectDir().'/'.$file->path);
    }

    /**
     * @param $uuid
     *
     * @throws \Exception
     *
     * @return File|null Return the file object
     */
    public function getFileFromUuid($uuid)
    {
        if ($path = $this->getPathFromUuid($uuid)) {
            if (is_dir($this->container->get('huh.utils.container')->getProjectDir().\DIRECTORY_SEPARATOR.$path)) {
                return null;
            }

            return new File($path);
        }
    }

    /**
     * @param      $uuid
     * @param bool $doNotCreate
     *
     * @throws \Exception
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

        $directory = ltrim(str_replace($this->container->getParameter('kernel.project_dir'), '', $file->dirname), '/');

        return ($directory ? $directory.'/' : '').$file->filename.uniqid($prefix, $moreEntropy).($file->extension ? '.'
                .$file->extension : '');
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

        if ('id-' != $name && !$this->container->get('huh.utils.string')->startsWith($fileName, 'id-')) {
            $name = preg_replace('/^(id-)/', '', $name);
        }

        if ($maxCount > 0) {
            $name = substr($name, 0, $maxCount - 1);
        }

        $directory = ltrim(str_replace($this->container->getParameter('kernel.project_dir'), '', $file->dirname), '/');

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
        if (\is_array($folder) && null !== $dc) {
            $callback = $folder;
            $folder = System::importStatic($callback[0])->{$callback[1]}($dc);
        } elseif (\is_callable($folder) && null !== $dc) {
            $method = $folder;
            $folder = $method($dc);
        } elseif (\is_string($folder)) {
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
            if (false === strpos($file, $this->container->getParameter('kernel.project_dir'))) {
                $file = $this->container->getParameter('kernel.project_dir').$file;
            }

            $handle = fopen($file, 'r');
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

    /**
     * convert pdf to png and return a preview file
     * delete the other png files.
     *
     * @deprecated Dublicate to PdfPreview util
     */
    public function getPreviewFromPdf(FilesModel $file, int $page = 0): FilesModel
    {
        $strippedName = str_replace('.'.$file->extension, '', $file->name);
        $previewFileName = 'preview-'.$strippedName.'.'.static::FILE_UTIL_CONVERT_FILE_TYPE;
        $folder = str_replace($file->name, '', $file->path);
        $target = $folder.\DIRECTORY_SEPARATOR.$previewFileName;

        // ghostscript
        /** @var Transcoder $transcoder */
        $transcoder = $this->framework->getAdapter(Transcoder::class)->create();
        $transcoder->toImage($file->path, $target);

        // get all created images
        $folderFiles = scandir($folder);
        $pdfPreviewFiles = [];
        $needle = '/preview-'.$strippedName.'*\.'.static::FILE_UTIL_CONVERT_FILE_TYPE.'/';

        foreach ($folderFiles as $file) {
            if (!preg_match($needle, $file)) {
                continue;
            }

            $pdfPreviewFiles[] = $file;
        }

        $preview = null;

        foreach ($pdfPreviewFiles as $key => $value) {
            if ($page != $key && file_exists($value)) {
                unlink($value);

                continue;
            }

            $preview = $value;
        }

        if (null === ($previewFile = $this->framework->getAdapter(FilesModel::class)->findByPath($preview))) {
            $previewFile = $this->framework->getAdapter(Dbafs::class)->addResource($folder.\DIRECTORY_SEPARATOR.$preview);
        }

        return $previewFile;
    }

    public function getFileIdFromPath($path)
    {
        if (is_dir(System::getContainer()->get('huh.utils.container')->getProjectDir().'/'.$path)) {
            if (null !== ($folder = (new Folder($path)))) {
                return $folder->getModel()->id;
            }
        } else {
            if (null !== ($file = (new File($path)))) {
                return $file->getModel()->id;
            }
        }

        return null;
    }

    public function getFolderContent($parentIds, $table, $options = [], $return = [])
    {
        $returnRows = $options['returnRows'] ?? false;
        $sorting = $options['sorting'] ?? false;
        $where = $options['where'] ?? '';

        if (!\is_array($parentIds)) {
            $parentIds = [$parentIds];
        }

        if (empty($parentIds)) {
            return $return;
        }

        $values = [];

        $parentIds = array_map(function ($pid) use (&$values, $returnRows) {
            $pid = \is_array($pid) ? $pid['id'] : $pid;

            $values[] = bin2hex(Validator::isStringUuid($pid) ? StringUtil::uuidToBin($pid) : $pid);

            return 'UNHEX(?)';
        }, $parentIds);

        $query = 'SELECT '.($returnRows ? '*' : 'id, pid').' FROM '.$table.' WHERE pid IN('.implode(',', $parentIds).')'.($where ? " AND $where" : '').($sorting ? ' ORDER BY '.$sorting : '');

        $objChilds = $this->framework->createInstance(Database::class)->prepare($query)->execute($values);

        if ($objChilds->numRows > 0) {
            if ($sorting) {
                $arrChilds = [];
                $arrOrdered = [];

                while ($objChilds->next()) {
                    $arrChilds[] = $returnRows ? $objChilds->row() : $objChilds->id;
                    $arrOrdered[$objChilds->pid][] = $returnRows ? $objChilds->row() : $objChilds->id;
                }

                foreach (array_reverse(array_keys($arrOrdered)) as $pid) {
                    $pos = (int) array_search($pid, $return);
                    array_insert($return, $pos + 1, $arrOrdered[$pid]);
                }

                $return = $this->getFolderContent($arrChilds, $table, $options, $return);
            } else {
                if ($returnRows) {
                    while ($objChilds->next()) {
                        $arrChilds[] = $objChilds->row();
                    }
                } else {
                    $arrChilds = $objChilds->fetchEach('id');
                }

                $return = array_merge($arrChilds, $this->getFolderContent($arrChilds, $table, $options, $return));
            }
        }

        return $return;
    }

    public static function getParentFoldersByUuid($uuid, array $config = [])
    {
        $returnRows = $config['returnRows'] ?? true;

        $parents = [];
        $firstSkipped = false;

        while ($uuid && null !== ($parent = System::getContainer()->get('huh.utils.model')->callModelMethod('tl_files', 'findByUuid', $uuid))) {
            $uuid = $parent->pid;

            // skip the file object passed into the function (only the parents should be returned)
            if (!$firstSkipped) {
                $firstSkipped = true;

                continue;
            }

            $parents[] = $returnRows ? $parent : $parent->id;
        }

        return $parents;
    }
}

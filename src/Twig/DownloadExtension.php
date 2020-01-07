<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Twig;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\Dbafs;
use Contao\Environment;
use Contao\File;
use Contao\Image;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DownloadExtension extends AbstractExtension
{
    /**
     * Get list of twig filters.
     *
     * @return array|\Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('download', [$this, 'getDownload']),
            new TwigFilter('download_link', [$this, 'getDownloadLink']),
            new TwigFilter('download_path', [$this, 'getDownloadPath']),
            new TwigFilter('download_data', [$this, 'getDownloadData']),
            new TwigFilter('download_title', [$this, 'getDownloadTitle']),
        ];
    }

    /**
     * Get download data based on given path/uuid.
     *
     * @param mixed $path File path/uuid
     * @param array $data Add custom data here
     *
     * @return array|null Download element data
     */
    public function getDownloadData($path, array $data = []): ?array
    {
        $path = Controller::replaceInsertTags($path);

        if (Validator::isUuid($path)) {
            $file = System::getContainer()->get('huh.utils.file')->getFileFromUuid($path);
        } else {
            try {
                $file = new File(urldecode($path));
            } catch (\Exception $e) {
                return null;
            }
        }

        if (null === $file) {
            return null;
        }

        $model = $file->getModel();

        if (null === $model) {
            try {
                Dbafs::addResource($file->path);
            } catch (\Exception $e) {
                return null;
            }

            if (null === $model) {
                return null;
            }
        }

        $requestedFile = System::getContainer()->get('huh.request')->getGet('file', true);

        // Send the file to the browser and do not send a 404 header (see #4632)
        if ('' != $requestedFile && $requestedFile == $file->path) {
            try {
                Controller::sendFileToBrowser($requestedFile);
            } catch (ResponseException $e) {
                $e->getResponse()->send();
            }

            return null;
        }

        $fileData['model'] = $file->getModel()->row();

        $allowedDownload = StringUtil::trimsplit(',', strtolower(Config::get('allowedDownload')));

        // Return if the file type is not allowed
        if (!\in_array($file->extension, $allowedDownload)) {
            return null;
        }

        if (!isset($data['linkTitle'])) {
            $fileData['linkTitle'] = StringUtil::specialchars($file->basename);
        }

        $strHref = Environment::get('request');

        // Remove an existing file parameter (see #5683)
        $strHref = System::getContainer()->get('huh.utils.url')->addQueryString('file='.System::urlEncode($file->value), $strHref);

        $fileData['link'] = $fileData['linkTitle'];
        $fileData['title'] = StringUtil::specialchars($data['titleText'] ?: sprintf($GLOBALS['TL_LANG']['MSC']['download'], $file->basename));
        $fileData['href'] = $strHref;
        $fileData['filesize'] = System::getReadableSize($file->filesize, 1);
        $fileData['icon'] = Image::getPath($file->icon);
        $fileData['mime'] = $file->mime;
        $fileData['extension'] = $file->extension;
        $fileData['path'] = $file->dirname;

        return array_merge($fileData, $data);
    }

    /**
     * Get download based on given path/uuid.
     *
     * @param mixed  $path     File path/uuid
     * @param bool   $download Return link as download link if true, as download path if false
     * @param array  $data     Add custom data here
     * @param string $template Use custom download template
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     *
     * @return string Download html element
     */
    public function getDownload($path, bool $download = true, array $data = [], string $template = '@HeimrichHannotContaoUtils/download.html.twig'): string
    {
        if (null === ($data = $this->getDownloadData($path, $data))) {
            return '';
        }

        if (false === $download) {
            $data['href'] = $data['model']['path'];
            $data['target'] = true;
        }

        try {
            return System::getContainer()->get('twig')->render($template, $data);
        } catch (\Twig_Error $e) {
        }

        return '';
    }

    /**
     * Get download link `?file=` based on given path/uuid.
     *
     * @param mixed $path File path/uuid
     * @param array $data Add custom data here
     *
     * @return string File download link
     */
    public function getDownloadLink($path, array $data = []): string
    {
        if (null === ($data = $this->getDownloadData($path, $data))) {
            return '';
        }

        return $data['href'];
    }

    /**
     * Get download path based on given path/uuid.
     *
     * @param mixed $path File path/uuid
     * @param array $data Add custom data here
     *
     * @return string File path
     */
    public function getDownloadPath($path, array $data = []): string
    {
        if (null === ($data = $this->getDownloadData($path, $data))) {
            return '';
        }

        return $data['model']['path'];
    }

    /**
     * Get download title based on given path/uuid.
     *
     * @param mixed $path File path/uuid
     * @param array $data Add custom data here
     *
     * @return string Download title
     */
    public function getDownloadTitle($path, array $data = []): string
    {
        if (null === ($data = $this->getDownloadData($path, $data))) {
            return '';
        }

        return $data['title'];
    }
}

<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
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
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Error\Error;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig_Error;

class DownloadExtension extends AbstractExtension
{
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var Utils
     */
    private $utils;
    /**
     * @var \Twig\Environment
     */
    private $twig;

    public function __construct(RequestStack $requestStack, Utils $utils, \Twig\Environment $twig)
    {
        $this->requestStack = $requestStack;
        $this->utils = $utils;
        $this->twig = $twig;
    }

    /**
     * Get list of twig filters.
     *
     * @return array|\Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('download', [$this, 'getDownload', ['deprecated' => true]]),
            new TwigFilter('download_link', [$this, 'getDownloadLink'], ['deprecated' => true]),
            new TwigFilter('download_path', [$this, 'getDownloadPath'], ['deprecated' => true]),
            new TwigFilter('download_data', [$this, 'getDownloadData'], ['deprecated' => true]),
            new TwigFilter('download_title', [$this, 'getDownloadTitle'], ['deprecated' => true]),
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
        if (Validator::isUuid($path)) {
            $path = $this->utils->file()->getPathFromUuid($path);
        } else {
            $path = Controller::replaceInsertTags($path);
        }

        if (!$path) {
            return null;
        }

        try {
            $file = new File(urldecode($path));
        } catch (\Exception $e) {
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

        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return null;
        }

        $requestedFile = $request->query->get('file');

        // Send the file to the browser and do not send a 404 header (see #4632)
        if ('' != $requestedFile && $requestedFile == $file->path) {
            try {
                ob_clean();
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

        if (!isset($data['titleText'])) {
            $data['titleText'] = sprintf($GLOBALS['TL_LANG']['MSC']['download'], $file->basename);
        }

        $strHref = Environment::get('request');

        // Remove an existing file parameter (see #5683)
        $strHref = $this->utils->url()->addQueryStringParameterToUrl('file='.System::urlEncode($file->value), $strHref);

        $fileData['link'] = $fileData['linkTitle'];
        $fileData['title'] = StringUtil::specialchars($data['titleText']);
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
            return $this->twig->render($template, $data);
        } catch (Twig_Error $e) {
        } catch (Error $error) {
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

<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FilesModel;

class FileUtil
{
    public function __construct(
        private ContaoFramework $contaoFramework,
        private string $projectDir
    ) {
    }

    /**
     * Get the path from a uuid.
     *
     * Options:
     * - checkIfExist: (bool) Enable check if the the file exist. Default true
     * - absolutePath: (bool) Return absolute path instead of relative path.
     *
     * @param array{
     *     checkIfExist?: bool,
     *     absolutePath?: bool
     * } $options Pass additional options.
     * @return string|null Return the path of the file, or null if not exists
     */
    public function getPathFromUuid(string $uuid, array $options = []): ?string
    {
        $file = $this->contaoFramework->getAdapter(FilesModel::class)->findByUuid($uuid);

        if (!$file) {
            return null;
        }

        $options = array_merge([
            'checkIfExist' => true,
            'absolutePath' => false,
        ], $options);

        $absoluteFilePath = $this->projectDir.\DIRECTORY_SEPARATOR.$file->path;

        if ($options['checkIfExist'] && !file_exists($absoluteFilePath)) {
            return null;
        }

        return $options['absolutePath'] ? $absoluteFilePath : $file->path;
    }
}

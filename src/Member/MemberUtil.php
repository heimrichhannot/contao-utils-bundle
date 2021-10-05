<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Member;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FilesModel;
use Contao\Folder;
use Contao\MemberModel;
use Contao\System;
use Contao\Validator;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Traits\PersonTrait;

class MemberUtil
{
    use PersonTrait;

    const TABLE = 'tl_member';

    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;
    /**
     * @var ModelUtil
     */
    protected $modelUtil;

    public function __construct(
        ContaoFrameworkInterface $framework,
        ModelUtil $modelUtil
    ) {
        $this->framework = $framework;
        $this->modelUtil = $modelUtil;
    }

    /**
     * Adds a new home dir to a member. Therefore a folder named with the members's id is created in $varRootFolder.
     *
     * @param            $member          MemberModel|int The member as object or member id
     * @param            $booleanProperty string The name of the boolean member property (e.g. "assignDir")
     * @param            $propertyName    string The name of the member property (e.g. "homeDir")
     * @param            $rootFolder      string|object The base folder as instance of \FilesModel, path string or uuid
     * @param bool|false $overwrite       bool Determines if an existing folder can be overridden
     *
     * @return bool|string returns true, if a directory has already been linked with the member, the folders uuid if successfully added and false if
     *                     errors occurred
     */
    public static function addHomeDir(
        $member,
        string $booleanProperty = 'assignDir',
        string $propertyName = 'homeDir',
        $rootFolder = 'files/members',
        $overwrite = false
    ) {
        if (null === ($member = is_numeric($member) ? System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_member', $member) : $member)) {
            return false;
        }

        // already set
        if ($member->{$booleanProperty} && $member->{$propertyName} && !$overwrite) {
            return true;
        }

        if (!($rootFolder instanceof FilesModel)) {
            if (Validator::isUuid($rootFolder)) {
                $folderModel = FilesModel::findByUuid($rootFolder);
                $path = $folderModel->path;
            } else {
                $path = $rootFolder;
            }
        } else {
            $path = $rootFolder->path;
        }

        $path = str_replace(System::getContainer()->getParameter('kernel.project_dir'), '', $path);

        if (!$path) {
            return false;
        }

        $member->{$booleanProperty} = true;
        $path = ltrim($path, '/').'/'.$member->id;

        $homeDir = new Folder($path);

        $member->{$propertyName} = $homeDir->getModel()->uuid;

        $member->save();

        return $homeDir->getModel()->uuid;
    }

    /**
     * Returns a member home dir and creates one, if desired.
     *
     * @param            $member          MemberModel|int The member as object or member id
     * @param            $booleanProperty string The name of the boolean member property (e.g. "assignDir")
     * @param            $propertyName    string The name of the member property (e.g. "homeDir")
     * @param            $rootFolder      string|FilesModel The base folder as instance of FilesModel, path string or uuid
     * @param bool|false $overwrite       bool Determines if an existing folder can be overridden
     *
     * @return bool|string returns the home dir or false if an error occurred
     */
    public static function getHomeDir(
        $member,
        string $booleanProperty = 'assignDir',
        string $propertyName = 'homeDir',
        $rootFolder = 'files/members',
        $overwrite = false
    ) {
        if (null === ($member = is_numeric($member) ? System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_member', $member) : $member)) {
            return false;
        }

        $varResult = static::addHomeDir($member, $booleanProperty, $propertyName, $rootFolder, $overwrite);

        if (false === $varResult) {
            return false;
        }

        return System::getContainer()->get('huh.utils.file')->getPathFromUuid($member->{$propertyName});
    }

    /**
     * Find active members by member group.
     *
     * Options (pass via options array):
     * - ignoreLogin: (bool) Ignore login field when check for active state. Default: false
     *
     * @return MemberModel|MemberModel[]|\Contao\Model\Collection|null
     */
    public function findActiveByGroups(array $groups, array $options = [])
    {
        if (empty($groups)) {
            return null;
        }

        /** @var $adapter MemberModel */
        if (null === $adapter = $this->framework->getAdapter(MemberModel::class)) {
            return null;
        }

        $t = $adapter->getTable();
        $time = \Date::floorToMinute();
        $values = [];

        $columns = ["($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'".($time + 60)."') AND $t.disable=''"];

        if (!isset($options['ignoreLogin']) || !$options['ignoreLogin']) {
            $columns[] = "$t.login='1'";
        }

        if (!empty(array_filter($groups))) {
            [$columns[], $tmpValues] = System::getContainer()->get('huh.utils.database')->createWhereForSerializedBlob('groups', array_filter($groups));
            $values = array_merge(array_values($values), array_values($tmpValues));
        }

        return $adapter->findBy($columns, $values, $options);
    }

    public function findOrCreate(string $email)
    {
        /** @var $adapter MemberModel */
        if (null === $adapter = $this->framework->getAdapter(MemberModel::class)) {
            return null;
        }

        $member = $adapter->findByEmail($email);

        if (null === $member) {
            $member = new \MemberModel();
            $member->dateAdded = time();
            $member->tstamp = time();
            $member->email = trim(strtolower($email));
            $member->save();
        }

        return $member;
    }
}

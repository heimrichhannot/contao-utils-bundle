<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util\User;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Model\Collection;
use Contao\UserModel;
use HeimrichHannot\UtilsBundle\Traits\PersonTrait;
use HeimrichHannot\UtilsBundle\Util\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Util\Model\ModelUtil;

class UserUtil
{
    use PersonTrait;

    /** @var DatabaseUtil */
    private $databaseUtil;
    /** @var ContaoFramework */
    private $contaoFramework;

    /**
     * UserUtil constructor.
     */
    public function __construct(
        private ModelUtil $modelUtil, DatabaseUtil $databaseUtil, ContaoFramework $contaoFramework)
    {
        $this->databaseUtil = $databaseUtil;
        $this->contaoFramework = $contaoFramework;
    }


    public function findActiveUsersByGroup(array $groups, array $options = []): ?Collection
    {
        return $this->findActiveByGroups($this->contaoFramework, $this->databaseUtil, UserModel::getTable(), $groups, $options);
    }

    /**
     * Returns all active users userGroups as a Collection of Models or null if user do not belong to any active userGroups
     *
     * @param int|UserModel $user
     * @return Collection|null
     */
    public function getActiveGroups(int|UserModel $user): ?Collection
    {
        if (!($user instanceof UserModel)) {
            $user = $this->contaoFramework->getAdapter(UserModel::class)->findByPk($user);
        }

        if (!$user) {
            return null;
        }

        return $this->loadUsersActiveGroups($user, $this->modelUtil, UserModel::getTable());
    }

    public function hasActiveGroup(int|UserModel $user, int $groupId): bool
    {
        if (!($user instanceof UserModel)) {
            $user = $this->contaoFramework->getAdapter(UserModel::class)->findByPk($user);
        }

        if (!$user) {
            return false;
        }

        return $this->loadHasActiveGroup($groupId, $user, $this->modelUtil, UserModel::getTable());
    }
}

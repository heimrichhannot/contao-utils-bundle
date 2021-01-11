<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\User;

use Contao\BackendUser;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\StringUtil;
use Contao\System;
use Contao\UserModel;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class UserUtil
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;
    /**
     * @var ModelUtil
     */
    protected $modelUtil;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * @return UserModel|UserModel[]|\Contao\Model\Collection|null
     */
    public function findActiveByGroups(array $groups, array $options = [])
    {
        if (empty($groups)) {
            return null;
        }

        /** @var $adapter UserModel */
        if (null === $adapter = $this->framework->getAdapter(UserModel::class)) {
            return null;
        }

        $t = $adapter->getTable();
        $time = \Date::floorToMinute();
        $values = [];

        $columns = ["($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'".($time + 60)."') AND $t.disable=''"];

        if (!empty(array_filter($groups))) {
            [$tmpColumns, $tmpValues] = System::getContainer()->get('huh.utils.database')->createWhereForSerializedBlob('groups', array_filter($groups));

            $columns[] = str_replace('?', $tmpValues[0], $tmpColumns);
        }

        return $adapter->findBy($columns, $values, $options);
    }

    public function hasAccessToField($table, $field)
    {
        $user = $this->framework->createInstance(BackendUser::class);

        if (null === ($objUser = $user) || !\is_array($user->alexf)) {
            return false;
        }

        return $objUser->isAdmin || \in_array($table.'::'.$field, $user->alexf);
    }

    public function isAdmin(): bool
    {
        $user = $this->framework->createInstance(BackendUser::class);

        if (null === $user || !\is_array($user->alexf)) {
            return false;
        }

        return $user->isAdmin;
    }

    public function getActiveGroups(int $userId, array $options = []): array
    {
        if (!$userId) {
            return [];
        }

        $modelUtil = System::getContainer()->get(ModelUtil::class);

        if (null === ($userModel = $modelUtil->findModelInstanceByIdOrAlias('tl_user', $userId, $options))) {
            return [];
        }

        $groups = StringUtil::deserialize($userModel->groups, true);

        $columns = ['tl_user_group.id IN('.implode(',', array_map('\intval', $groups)).')'];

        $modelUtil->addPublishedCheckToModelArrays('tl_user_group', 'disable', 'start', 'stop', $columns, ['invertPublishedField' => true]);

        if (null === ($groupModelCollection = $modelUtil->findModelInstancesBy('tl_user_group', $columns, []))) {
            return [];
        }

        return $groupModelCollection->getModels();
    }

    public function hasActiveGroup(int $userId, int $group): bool
    {
        $activeGroups = $this->getActiveGroups($userId);

        foreach ($activeGroups as $activeGroup) {
            if ($group === (int) ($activeGroup->id)) {
                return true;
            }
        }

        return false;
    }
}

<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Traits;

use Contao\Model\Collection;
use Contao\StringUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

/**
 * Trait PersonTrait.
 *
 * @param ModelUtil $modelUtil
 */
trait PersonTrait
{
    /**
     * @param int $userId
     *
     * Returns all active users userGroups as a Collection of Models or null if user do not belong to any active userGroups
     */
    public function getActiveGroups(int $userId): ?Collection
    {
        if (null === ($userModel = $this->modelUtil->findModelInstanceByPk(static::TABLE, $userId))) {
            return null;
        }

        if (empty($groups = StringUtil::deserialize($userModel->groups, true))) {
            return null;
        }

        $columns = [static::TABLE.'_group.id IN('.implode(',', array_map('\intval', $groups)).')'];

        $this->modelUtil->addPublishedCheckToModelArrays(static::TABLE.'_group', 'disable', 'start', 'stop', $columns, ['invertPublishedField' => true]);

        return $this->modelUtil->findModelInstancesBy(static::TABLE.'_group', $columns, []);
    }

    /**
     * @param int $groupId
     *
     * Checks given user group is active and given user belongs to this group
     */
    public function hasActiveGroup(int $userId, int $groupId): bool
    {
        $activeGroups = $this->getActiveGroups($userId);

        if (!$activeGroups) {
            return false;
        }

        foreach ($activeGroups as $group) {
            if ((int) ($group->id) === $groupId) {
                return true;
            }
        }

        return false;
    }
}

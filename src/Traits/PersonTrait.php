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
     * @return array
     *               returns empty array or array of group Models
     */
    public function getActiveGroups(int $userId): ?Collection
    {
        if (!$userModel = $this->modelUtil->findModelInstanceByPk($this::TABLE, $userId)) {
            return null;
        }

        if (empty($groups = StringUtil::deserialize($userModel->groups, true))) {
            return null;
        }

        $columns = [$this::TABLE.'_group.id IN('.implode(',', array_map('\intval', $groups)).')'];

        $this->modelUtil->addPublishedCheckToModelArrays($this::TABLE.'_group', 'disable', 'start', 'stop', $columns, ['invertPublishedField' => true]);

        return $this->modelUtil->findModelInstancesBy($this::TABLE.'_group', $columns, []);
    }

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

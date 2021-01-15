<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Traits;

use Contao\StringUtil;

trait PersonTrait
{
    /**
     * @return array
     *               returns empty array or array of group Models
     */
    public function getActiveGroups(int $userId, string $source): array
    {
        if (!$userId) {
            return [];
        }

        if (null === ($userModel = $this->modelUtil->findModelInstanceByIdOrAlias($source, $userId))) {
            return [];
        }

        if (empty($groups = StringUtil::deserialize($userModel->groups, true))) {
            return [];
        }

        $columns = [$source.'_group.id IN('.implode(',', array_map('\intval', $groups)).')'];

        $this->modelUtil->addPublishedCheckToModelArrays($source.'_group', 'disable', 'start', 'stop', $columns, ['invertPublishedField' => true]);

        if (null === ($groupModelCollection = $this->modelUtil->findModelInstancesBy($source.'_group', $columns, []))) {
            return [];
        }

        return $groupModelCollection->getModels();
    }

    public function hasActiveGroup(int $userId, int $groupId, string $source): bool
    {
        $activeGroups = $this->getActiveGroups($userId, $source);

        foreach ($activeGroups as $group) {
            if ((int) ($group->id) === $groupId) {
                return true;
            }
        }

        return false;
    }
}

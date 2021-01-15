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
    public function getActiveGroups(int $personId, string $personTable, string $personGroupTable, array $personOptions = []): array
    {
        if (!$personId) {
            return [];
        }

        if (null === ($userModel = $this->modelUtil->findModelInstanceByIdOrAlias($personTable, $personId, $personOptions))) {
            return [];
        }

        $groups = StringUtil::deserialize($userModel->groups, true);

        if (empty($groups)) {
            return [];
        }

        $columns = [$personGroupTable.'.id IN('.implode(',', array_map('\intval', $groups)).')'];
        $this->modelUtil->addPublishedCheckToModelArrays($personGroupTable, 'disable', 'start', 'stop', $columns, ['invertPublishedField' => true]);

        if (null === ($groupModelCollection = $this->modelUtil->findModelInstancesBy($personGroupTable, $columns, []))) {
            return [];
        }

        return $groupModelCollection->getModels();
    }

    public function hasActiveGroup(int $personId, string $personTable, int $personGroupId, string $personGroupTable): bool
    {
        $activeGroups = $this->getActiveGroups($personId, $personTable, $personGroupTable);

        foreach ($activeGroups as $activeGroup) {
            if ($personGroupId === (int) ($activeGroup->id)) {
                return true;
            }
        }

        return false;
    }
}

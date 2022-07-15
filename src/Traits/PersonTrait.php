<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Traits;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Date;
use Contao\Model;
use Contao\Model\Collection;
use Contao\StringUtil;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

/**
 * Trait PersonTrait.
 *
 * @internal This trait is not covered by BC promise and only for internal usage
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

        if ($this->modelUtil instanceof \HeimrichHannot\UtilsBundle\Util\Model\ModelUtil) {
            /* @var \HeimrichHannot\UtilsBundle\Util\Model\ModelUtil $this->modelUtil */
            $this->modelUtil->addPublishedCheckToModelArrays(static::TABLE.'_group', $columns, [
                'publishedField' => 'disable',
                'invertPublishedField' => true,
            ]);
        } else {
            $this->modelUtil->addPublishedCheckToModelArrays(static::TABLE.'_group', 'disable', 'start', 'stop', $columns, ['invertPublishedField' => true]);
        }

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

    /**
     * @throws \Exception
     */
    protected function findActiveByGroups(ContaoFramework $contaoFramework, DatabaseUtil $databaseUtil, string $table, array $groups, array $options = []): ?Collection
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = $contaoFramework->getAdapter(Model::class)->getClassFromTable($table);

        if (!\is_array($groups) || empty($groups = array_filter($groups, function ($k) {
            return !empty($k) && is_numeric($k);
        }))) {
            return null;
        }

        /** @var Model $adapter */
        $adapter = $contaoFramework->getAdapter($modelClass);

        $time = Date::floorToMinute();
        $values = [];

        $columns = ["($table.start='' OR $table.start<='$time') AND ($table.stop='' OR $table.stop>'".($time + 60)."') AND $table.disable=''"];

        [$columns[], $tmpValues] = $databaseUtil->createWhereForSerializedBlob('groups', $groups);
        $values = array_merge(array_values($values), array_values($tmpValues));

        return $adapter->findBy($columns, $values, $options);
    }
}

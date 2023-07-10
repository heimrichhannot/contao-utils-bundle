<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Traits;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Date;
use Contao\MemberModel;
use Contao\Model;
use Contao\Model\Collection;
use Contao\StringUtil;
use Contao\UserModel;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Util\Model\ModelUtil;

/**
 * Trait PersonTrait.
 *
 * @internal This trait is not covered by BC promise and only for internal usage
 *
 */
trait PersonTrait
{
    protected function loadUsersActiveGroups(MemberModel|UserModel $userModel, ModelUtil $modelUtil, string $table): ?Collection
    {
        if (empty($groups = StringUtil::deserialize($userModel->groups, true))) {
            return null;
        }

        $groupTable = $table.'_group';

        $columns = [$groupTable.'.id IN('.implode(',', array_map('\intval', $groups)).')'];

        $modelUtil->addPublishedCheckToModelArrays($groupTable, $columns, [
            'publishedField' => 'disable',
            'invertPublishedField' => true,
        ]);

        return $modelUtil->findModelInstancesBy($groupTable, $columns, []);
    }

    protected function loadHasActiveGroup(int $groupId, MemberModel|UserModel $user, ModelUtil $modelUtil, string $table): bool
    {
        $activeGroups = $this->loadUsersActiveGroups($user, $modelUtil, $table);

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
        $columns[] = '';

        [$columns[], $tmpValues] = $databaseUtil->createWhereForSerializedBlob('groups', $groups);
        $values = array_merge(array_values($values), array_values($tmpValues));

        return $adapter->findBy($columns, $values, $options);
    }
}

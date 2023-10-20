<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util\User;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Date;
use Contao\MemberModel;
use Contao\Model;
use Contao\Model\Collection;
use Contao\StringUtil;
use Contao\UserModel;
use HeimrichHannot\UtilsBundle\Util\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Util\Model\ModelUtil;

class UserUtil
{
    public const TYPE_USER = 'user';
    public const TYPE_MEMBER = 'member';

    /**
     * UserUtil constructor.
     */
    public function __construct(
        private ModelUtil $modelUtil,
        private DatabaseUtil $databaseUtil,
        private ContaoFramework $contaoFramework
    )
    {
    }


    public function findActiveUsersByGroup(array $groups, string $type = self::TYPE_USER, array $options = []): ?Collection
    {
        $table = match ($type) {
            self::TYPE_USER => UserModel::getTable(),
            self::TYPE_MEMBER => MemberModel::getTable(),
            default => throw new \InvalidArgumentException(sprintf('Invalid type "%s" given.', $type)),
        };

        /** @var class-string<Model> $modelClass */
        $modelClass = $this->contaoFramework->getAdapter(Model::class)->getClassFromTable($table);

        if (!$modelClass) {
            return null;
        }

        if (!\is_array($groups) || empty($groups = array_filter($groups, function ($k) {
                return !empty($k) && is_numeric($k);
            }))) {
            return null;
        }

        /** @var Model $adapter */
        $adapter = $this->contaoFramework->getAdapter($modelClass);

        $time = Date::floorToMinute();
        $values = [];

        $columns = ["($table.start='' OR $table.start<='$time') AND ($table.stop='' OR $table.stop>'".($time + 60)."') AND $table.disable=''"];
        $columns[] = '';

        [$columns[], $tmpValues] = $this->databaseUtil->createWhereForSerializedBlob('groups', $groups);
        $values = array_merge(array_values($values), array_values($tmpValues));

        return $adapter->findBy($columns, $values, $options);
    }

    /**
     * Returns all active users userGroups as a Collection of Models or null if user do not belong to any active userGroups
     */
    public function getActiveGroups(UserModel|MemberModel $user): ?Collection
    {
        if (empty($groups = StringUtil::deserialize($user->groups, true))) {
            return null;
        }

        if ($user instanceof MemberModel) {
            $groupTable = 'tl_member_group';
        } else {
            $groupTable = 'tl_user_group';
        }

        $columns = [$groupTable.'.id IN('.implode(',', array_map('\intval', $groups)).')'];

        $this->modelUtil->addPublishedCheckToModelArrays($groupTable, $columns, [
            'publishedField' => 'disable',
            'invertPublishedField' => true,
        ]);

        return $this->modelUtil->findModelInstancesBy($groupTable, $columns, []);
    }

    /**
     * Returns true if the user or member (frontend user) is member of given group, false otherwise.
     */
    public function hasActiveGroup(UserModel|MemberModel $user, int $groupId): bool
    {
        $activeGroups = $this->getActiveGroups($user);

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

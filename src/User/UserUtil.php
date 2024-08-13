<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\User;

use Contao\BackendUser;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use Contao\UserModel;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Traits\PersonTrait;

/**
 * Class UserUtil.
 */
class UserUtil
{
    use PersonTrait;

    const TABLE = 'tl_user';

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
     * @return UserModel|UserModel[]|\Contao\Model\Collection|null
     *
     * @deprecated use Utils service instead
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
            [$columns[], $tmpValues] = System::getContainer()->get('huh.utils.database')->createWhereForSerializedBlob('groups', array_filter($groups));
            $values = array_merge(array_values($values), array_values($tmpValues));
        }

        return $adapter->findBy($columns, $values, $options);
    }

    public function hasAccessToField($table, $field): bool
    {
        $user = $this->framework->createInstance(BackendUser::class);

        if ($user === null) {
            return false;
        }

        if ($user->isAdmin) {
            return true;
        }

        return \is_array($user->alexf)
            && \in_array("$table::$field", $user->alexf);
    }

    public function isAdmin(): bool
    {
        $user = $this->framework->createInstance(BackendUser::class);

        if (null === $user || !\is_array($user->alexf)) {
            return false;
        }

        return $user->isAdmin;
    }
}

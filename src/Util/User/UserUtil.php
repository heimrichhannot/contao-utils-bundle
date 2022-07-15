<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Util\User;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Model\Collection;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Traits\PersonTrait;
use HeimrichHannot\UtilsBundle\Util\Model\ModelUtil;

class UserUtil
{
    use PersonTrait;

    const TABLE = 'tl_user';

    /** @var ModelUtil */
    protected $modelUtil;
    /** @var DatabaseUtil */
    private $databaseUtil;
    /** @var ContaoFramework */
    private $contaoFramework;

    /**
     * UserUtil constructor.
     */
    public function __construct(ModelUtil $modelUtil, DatabaseUtil $databaseUtil, ContaoFramework $contaoFramework)
    {
        $this->modelUtil = $modelUtil;
        $this->databaseUtil = $databaseUtil;
        $this->contaoFramework = $contaoFramework;
    }

    public function findActiveUsersByGroup(array $groups, array $options = []): ?Collection
    {
        return $this->findActiveByGroups($this->contaoFramework, $this->databaseUtil, static::TABLE, $groups, $options);
    }
}

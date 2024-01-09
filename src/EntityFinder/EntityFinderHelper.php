<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\EntityFinder;

use Contao\ContentModel;
use Contao\Database;
use Contao\Model\Collection;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\Validator;
use Doctrine\DBAL\Connection;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;

class EntityFinderHelper
{
    /**
     * @var Utils
     */
    private $utils;
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Utils $utils, Connection $connection)
    {
        $this->utils = $utils;
        $this->connection = $connection;
    }

    /**
     * Search within serialized array fields of the model entity.
     *
     * @param string $type   Module type
     * @param string $field  Field with serialized data
     * @param array  $values Values to search for in serialized data field
     *
     * @throws \Exception
     */
    public function findModulesByTypeAndSerializedValue(string $type, string $field, array $values): ?Collection
    {
        $result = $this->utils->database()->createWhereForSerializedBlob(ModuleModel::getTable().'.'.$field, $values);
        $columns = [$result->createAndWhere(), ModuleModel::getTable().'.type=?'];
        $values = [$result->values, $type];

        return ModuleModel::findBy($columns, $values);
    }

    /**
     * Find frontend modules by insert inserttags like insert_module oder insert_article.
     *
     * @param string $type The module type
     * @param string $field The tl_module field
     * @param string $inserttag The inserttag to search for, for example insert_module
     * @param int $id The element id to search for, for example the module id (as used in {{insert_module::1}}, would be 1 in this case)
     * @return array The found module ids
     * @throws \Exception
     */
    public function findModulesByInserttag(string $type, string $field, string $inserttag, int $id): array
    {
        if (!Validator::isAlias($field)) {
            throw new \Exception('Invalid field name '.$field.'given.');
        }
        if (!Validator::isAlias($inserttag)) {
            throw new \Exception('Invalid inserttag '.$inserttag.'given.');
        }
        $result = Database::getInstance()
            ->prepare("SELECT id FROM tl_module
                        WHERE type=?
                        AND (
                            $field LIKE '%{{".$inserttag."::".$id."}}%'
                            OR $field LIKE '%{{".$inserttag."::".$id."::%')")
            ->execute($type);

        return $result->fetchEach('id');
    }


    /**
     * Find content elements by insert inserttags like insert_module oder insert_article.
     *
     * @param string $type The element type
     * @param string $field The tl_content field
     * @param string $inserttag The inserttag to search for, for example insert_module
     * @param int $id The element id to search for, for example the module id (as used in {{insert_module::1}}, would be 1 in this case)
     * @return array The found content element ids
     * @throws \Exception
     */
    public function findContentElementByInserttag(string $type, string $field, string $inserttag, int $id): array
    {
        if (!Validator::isAlias($field)) {
            throw new \Exception('Invalid field name '.$field.'given.');
        }
        if (!Validator::isAlias($inserttag)) {
            throw new \Exception('Invalid inserttag '.$inserttag.'given.');
        }
        $result = Database::getInstance()
            ->prepare("SELECT id FROM tl_content
                        WHERE type=?
                        AND (
                            $field LIKE '%{{".$inserttag."::".$id."}}%'
                            OR $field LIKE '%{{".$inserttag."::".$id."::%')")
            ->execute($type);

        return $result->fetchEach('id');
    }
}

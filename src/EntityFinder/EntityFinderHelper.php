<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\EntityFinder;

use Contao\ContentModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\Model;
use Contao\Model\Collection;
use Contao\ModuleModel;
use Contao\Validator;
use Doctrine\DBAL\Connection;
use HeimrichHannot\UtilsBundle\Util\Utils;

class EntityFinderHelper
{
    public function __construct(
        private readonly Utils           $utils,
        private readonly ContaoFramework $framework,
        private readonly Connection      $connection,
    )
    {
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
        $blobQuery = $this->utils->database()->createWhereForSerializedBlob(ModuleModel::getTable().'.'.$field, $values);
        $columns = [$blobQuery->createOrWhere()];
        $values = $blobQuery->values;

        $columns[] = ModuleModel::getTable().'.type=?';
        $values[] = $type;

        return $this->framework->getAdapter(ModuleModel::class)->findBy($columns, $values);
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
     * @return array<ContentModel> The found content element ids
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

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function fetchModelOrData(string $table, int|string $idOrAlias): ?Model
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = $this->framework->getAdapter(Model::class)->getClassFromTable($table);

        if (!$modelClass || !class_exists($modelClass)) {
            if (!$this->connection->createSchemaManager()->tablesExist([$table])) {
                return null;
            }
            if (is_string($idOrAlias)) {
                $result = $this->connection->executeQuery("SELECT * FROM $table WHERE alias=?", [$idOrAlias]);
                if ($result->rowCount() === 0) {
                    return null;
                }
                return $this->anonymousModel($table, $result->fetchAssociative());
            }
            if (is_numeric($idOrAlias)) {
                if ($idOrAlias != (int) $idOrAlias) {
                    return null;
                }

                $result = $this->connection->executeQuery("SELECT * FROM $table WHERE id=?", [(int)$idOrAlias]);
                if ($result->rowCount() === 0) {
                    return null;
                }
                return $this->anonymousModel($table, $result->fetchAssociative());
            }
        }

        return $this->framework->getAdapter($modelClass)->findByIdOrAlias($idOrAlias);
    }

    private function anonymousModel(string $table, array $data): Model
    {
        return new class($table, $data) extends Model {

            protected $blnPreventSaving = true;

            public function __construct(string $table, array $data = [])
            {
                $this->strTable = $table;
                $this->setRow($data);
            }

            public function __set($strKey, $varValue)
            {
                if (isset($this->arrData[$strKey]) && $this->arrData[$strKey] === $varValue)
                {
                    return;
                }

                $this->arrData[$strKey] = $varValue;
            }

            public function setRow(array $arrData)
            {
                foreach ($arrData as $k => $v)
                {
                    if (static::isJoinedField($k))
                    {
                        unset($arrData[$k]);
                    }
                }

                $this->arrData = $arrData;

                return $this;
            }
        };
    }
}

<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Model;

use Contao\Database;
use Contao\System;

class CfgTagModel extends \Model
{
    protected static $strTable = 'tl_cfg_tag';

    public static function findAll(array $arrOptions = [])
    {
        return parent::findAll($arrOptions);
    }

    /**
     * @param       $source
     * @param array $arrOptions
     *
     * @return @return static|Model\Collection|null A model, model collection or null if the result is empty
     */
    public static function findAllBySource($source, array $arrOptions = [])
    {
        return parent::findBy('source', $source, $arrOptions);
    }

    public static function findBy($column, $value, array $arrOptions = [])
    {
        return parent::findBy($column, $value, $arrOptions);
    }

    public static function getSourcesAsOptions(\DataContainer $dc)
    {
        $options = [];
        $tags = System::getContainer()->get('contao.framework')->getAdapter(Database::class)->getInstance()->prepare('SELECT source FROM tl_cfg_tag GROUP BY source')->execute();

        if (null !== $tags) {
            $options = $tags->fetchEach('source');

            asort($options);
        }

        return $options;
    }
}

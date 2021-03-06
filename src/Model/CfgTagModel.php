<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Model;

use Contao\Database;
use Contao\System;

if (class_exists('Codefog\TagsBundle\Model\TagModel')) {
    class TagModelBase extends \Codefog\TagsBundle\Model\TagModel
    {
    }
} else {
    class TagModelBase extends \Contao\Model
    {
    }
}
class CfgTagModel extends TagModelBase
{
    protected static $strTable = 'tl_cfg_tag';

    /**
     * @param $source
     *
     * @return \Contao\Model\Collection|static|null
     */
    public function findAllBySource($source, array $arrOptions = [])
    {
        /** @var CfgTagModel $adapter */
        if (null === ($adapter = System::getContainer()->get('contao.framework')->getAdapter(self::class))) {
            return null;
        }

        return $adapter->findBy('source', $source, $arrOptions);
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

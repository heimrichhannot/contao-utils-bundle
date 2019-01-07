<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Model;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\Model;
use Contao\System;

class CfgTagModel extends Model
{
    protected static $strTable = 'tl_cfg_tag';

    /** @var ContaoFrameworkInterface */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * @param       $source
     * @param array $arrOptions
     *
     * @return \Contao\Model\Collection|null|static
     */
    public function findAllBySource($source, array $arrOptions = [])
    {
        /** @var CfgTagModel $adapter */
        if (null === ($adapter = $this->framework->getAdapter(self::class))) {
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

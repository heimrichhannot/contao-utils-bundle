<?php

\Contao\System::getContainer()->get('huh.utils.cache.database_tree')->registerDcaToCacheTree('tl_page', ['type = ?'], ['root'], ['order' => 'sorting']);
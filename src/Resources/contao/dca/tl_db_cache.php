<?php

$GLOBALS['TL_DCA']['tl_db_cache'] = [
    'config' => [
        'dataContainer'     => 'Table',
        'enableVersioning'  => false,
        'sql'               => [
            'keys' => [
                'id' => 'primary'
            ]
        ]
    ],
    'fields' => [
        'id'        => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp'    => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'expiration' => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'cacheKey'       => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'cacheValue'     => [
            'sql' => "mediumtext NULL"
        ],
    ]
];

<?php
return [
	'doctrine' => [
		'connection' => [
			'orm_default' => [
				'driverClass' => \Doctrine\DBAL\Driver\PDOMySql\Driver::class,
// 				'url' => 'mysql://root:root@192.168.1.205/oauth2server',
				'host'     => env('MYSQL_DB_HOST', '127.0.0.1'),
				'port'     => env('MYSQL_DB_PORT', 3306),
				'user'     => env('MYSQL_DB_USERNAME', 'root'),
				'password' => env('MYSQL_DB_PASSWORD', 'root'),
				'dbname'   => env('MYSQL_DB_NAME', 'oauth2server'),
			],
		],
        'orm'        => [
            'auto_generate_proxy_classes' => false,
            'proxy_dir'                   => './data/cache/EntityProxy',
            'proxy_namespace'             => 'EntityProxy',
            'underscore_naming_strategy'  => true,

        ],
		'annotation' => [
			'metadata' => [
                'src/App/src/Entity',
                'src/OAuth2Server/src/Entity'
            ]
		],
	],
];
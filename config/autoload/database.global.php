<?php
return [
	'db' => [
		'driver'   => 'Pdo',
		'dsn'      => 'mysql:host='.env('MYSQL_DB_HOST', '127.0.0.1').';port='.env('MYSQL_DB_PORT', 3306).';dbname=' . env('MYSQL_DB_NAME', 'admin_tool'),
		'database' => env('MYSQL_DB_NAME', 'admin_tool'),
		'user'     => env('MYSQL_DB_USERNAME', 'root'),
        'password' => env('MYSQL_DB_PASSWORD', 'root'),
		'driver_options' => array(
			\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8"
		),
		'adapters' => [
			'passport_db' => [
				'driver'   => env('SQLSERVER_PASSPORT_DRIVER','Pdo'),
				'dsn'      => env('SQLSERVER_PASSPORT_DSN','dblib:host=172.31.6.40:1433;dbname=chess_authentication'),
				'charset' => 'UTF-8',
				'pdotype' => env('SQLSERVER_PASSPORT_PDOTYPE','dblib'),
				'user'     => env('SQLSERVER_PASSPORT_USERNAME','user_exec'),
                'username'     => env('SQLSERVER_PASSPORT_USERNAME','user_exec'),
				'password' => env('SQLSERVER_PASSPORT_PASSWORD','MovrvoGCaXUsqbuyDvlB'),
                'hostname' => env('SQLSERVER_PASSPORT_HOSTNAME','172.31.6.40'),
                'dbname'   => env('SQLSERVER_PASSPORT_DBNAME','chess_authentication'),
			],
			'payment_db' => [
				'driver'   => env('SQLSERVER_PAYMENT_DRIVER','Pdo'),
                'dsn'      => env('SQLSERVER_PAYMENT_DSN', 'dblib:host=172.31.6.40:1433;dbname=gamble_payment'),
				'charset' => 'UTF-8',
				'pdotype' => env('SQLSERVER_PAYMENT_PDOTYPE','dblib'),
                'user'     => env('SQLSERVER_PAYMENT_USERNAME','user_exec'),
                'username'     => env('SQLSERVER_PAYMENT_USERNAME','user_exec'),
                'password' => env('SQLSERVER_PAYMENT_PASSWORD','MovrvoGCaXUsqbuyDvlB'),
                'dbname'   => env('SQLSERVER_PAYMENT_DBNAME','gamble_payment'),
                'hostname' => env('SQLSERVER_PAYMENT_HOSTNAME','172.31.6.40'),
			]
		]
    ],
	'mongodb' => [
		'uri' => env('MONGO_DB_URI', 'mongodb://127.0.0.1:27017'),
		'uri_options' => [
			'readPreference' => 'secondaryPreferred',
			'username' => env('MONGO_DB_USERNAME', null),
			'password' => env('MONGO_DB_PASSWORD', null),
			'ssl' => env('MONGO_DB_SSL', false),
			'authSource' => env('MONGO_DB_AUTH_SOURCE', 'passport'),
			'replicaSet' => env('MONGO_DB_REPLICASET', null)
		],
		'driver_options' => [
			'typeMap' => [
				'root' => 'array', 'document' => 'array', 'array' => 'array'
			],
		]
	],
    'paymentMongoDB' => [
        'uri' => env('MONGO_DB_URI', 'mongodb://127.0.0.1:27017'),
        'uri_options' => [
            'readPreference' => 'secondaryPreferred',
            'username' => env('MONGO_DB_USERNAME', null),
            'password' => env('MONGO_DB_PASSWORD', null),
            'ssl' => env('MONGO_DB_SSL', false),
            'authSource' => defined('MONGO_DB_PAYMENT_SOURCE') ? MONGO_DB_PAYMENT_SOURCE : env('MONGO_DB_PAYMENT_SOURCE', 'payment'),
            'replicaSet' => env('MONGO_DB_REPLICASET', null)
        ],
        'driver_options' => [
            'typeMap' => [
                'root' => 'array', 'document' => 'array', 'array' => 'array'
            ],
        ]
    ],
	'predis_servers' => [
		[
			'host' => '127.0.0.1',
			'port' => 6379
		]
	]
];

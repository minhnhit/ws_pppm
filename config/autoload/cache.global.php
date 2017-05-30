<?php
$caches = ['caches' => []];

$caches['caches']['Cache\Persistence'] = [
    'adapter' => 'filesystem',
    'ttl' => 86400,
    'options' => [
        'cache_dir' => __DIR__ . '/../../data/cache/'
    ]
];

if (extension_loaded('redis')) {
	$caches['caches']['Cache\Transient'] = [
        'adapter' => 'redis',
        'ttl' => 60,
        'plugins' => [
            'exception_handler' => [
                'throw_exceptions' => true,
            ],
        ],
        'options' => [
            'server' => [
                'host' => '127.0.0.1',
                'port' => '6379',
            ],
        ]
	];
}
return $caches;
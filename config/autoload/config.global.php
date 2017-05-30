<?php
return [
    'translator' => [
        'locale' => 'en_US',
        'translation_file_patterns' => [
            [
                'type'     => 'gettext',
                'base_dir' => getcwd() .  '/data/language',
                'pattern'  => '%s.mo',
            ],
        ],
    ],
    'restrict_app' => [
        'path' => ['/doc.html', '/generate/*'],
        'passthrough' => []
    ],
	'get-config' => [
		'payment' => ['cardList' => ['VINA', 'MOBI', 'VT']]
	]
];
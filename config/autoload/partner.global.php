<?php
return [
	'partner' => [
        '1pay' => [
            'type' => 'smsplus', //smsplus
            'sms' => [
                'key' => 'z5iztpi3othz1rqqo87u',
                'secret' => 'orx7wnkunfrqo2mr8xf1j8pt6yapxlmu'
            ],
            'smsplus' => [
                'key' => 'ftql5xqutfgazxjjrrq6',
                'secret' => 'usw2fbnxhfi97kca3iywqrsedzbdabm6'
            ]
        ],
		'b1' => [
			'password' => 'test',
			'rateBuyCard' => '0.23',
			'rateMatch' => '0.1',
			'cardRate' => [
				'VINA' => '0.1',
				'MOBI' => '0.1',
				'VT'   => '0.1',
			]
		],
		'ttkn' => [
				'password' => 'test',
				'rateBuyCard' => '0.23',
				'rateMatch' => '0.1',
				'cardRate' => [
						'VINA' => '0.1',
						'MOBI' => '0.1',
						'VT'   => '0.1',
				]
		],
		'b2' => [
			'rateBuyCard' => '0.01',
			'rateMatch' => '0.01',
            'password' => 'test',
		],
        'c1' => [
            'rateBuyCard' => '0.23',
            'rateMatch' => '0.1',
            'rateGold' => '0.01',
            'cashoutRateGold' => 1,
            'password' => 'test',
            '_GAME_URL' => 'http://123.30.173.34:8080/IPN2',
            'secret_key' => 'exdEbh8ps5FmoHeyBRUay7UvHhfdd'
        ],
	],
];
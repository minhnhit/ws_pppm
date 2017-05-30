<?php
return [
    'mail' => [
        'transport' => [
            'class' => 'Zend\Mail\Transport\Sendmail',
            'options' => [
                'host'              => env('MAIL_SERVER_HOST', 'mail.568play.vn'),
                'connection_class'  => 'plain',
                'connection_config' => [
                    'username' => env('MAIL_SERVER_USERNAME', 'service@568play.vn'),
                    'password' => env('MAIL_SERVER_PASSWORD', '123456789!@#$'),
                    'ssl' => env('MAIL_SERVER_SSL', 'tls')
                ],
            ],
        ],
    ],
    'email' => [
        "active" => true,
        "defaults" => [
                "layout_name" => "default",
                "template_name" => "default",
                "from_email" => env('MAIL_SERVER_FROM', "noreply@568e.vn"),
                "from_name" => env('MAIL_SERVER_NAME', "568E"),
                "reply_to" => env('MAIL_SERVER_REPLY_TO', ""),
                "reply_to_name" => env('MAIL_SERVER_REPLY_TO_NAME', "")
        ],
        "emails" => [
            "admin" => env('ADMIN_EMAIL', "anhnv@568e.vn"),
        ],
        "cc_emails" => [
// 			"tunh@568e.vn" => 'SystemTeam',
        ],
        "template_path_stack" => [
            __DIR__ . "/../../src/App/templates/email/",
        ],
        'template_vars' => [
            "company" => "service@568e.vn",
            "slogan" => "568E",
            "baseUrl" => "https://id.568play.vn"
        ],
        'relay' => [
            'active'    => true,
            'host'      => env('MAIL_SERVER_HOST', 'mail.568play.vn'),
            'port'      => env('MAIL_SERVER_PORT', 25), //it could be empty
            'username'  => env('MAIL_SERVER_USERNAME', 'service@568play.vn'),
            'password'  => env('MAIL_SERVER_PASSWORD', '123456789!@#$'),
            'ssl'       => env('MAIL_SERVER_SSL', '') // it could be empty
        ]
    ]
];

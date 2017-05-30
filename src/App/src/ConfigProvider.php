<?php

namespace App;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates'    => $this->getTemplates(),
            'app' => [
                'authentication' => [
                    'default_redirect_to' => '/'
                ]
            ],
            'doctrine'     => [
                'driver' => [
                    'orm_default' => [
         				'class' => \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain::class,
                        'drivers' => [
                            'App\Entity' => 'my_entity',
                        ],
                    ],
                    'my_entity' => [
                        'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                        'cache' => 'array',
                        'paths' => [
                            __DIR__ . '/Entity',
                        ]
                    ],
                ],
            ],
            'swagger' => [
                'paths' => [
                    __DIR__ . '/../../'
                ],
                'resource_options' => [
                    'output' => 'array',
                    'json_pretty_print' => true, // for outputtype 'json'
                    'defaultApiVersion' => null,
                    'defaultBasePath' => null, // e.g. /api
                    'defaultHost' => null, // example.com
                    'schemes' => null, // e.g. ['http', 'https'],
                ],
            ]
        ];
    }

    /**
     * Returns the container dependencies
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            'invokables' => [
                Action\PingAction::class => Action\PingAction::class,
                Action\GenAction::class => Action\GenAction::class,
            ],
            'factories'  => [
                Action\HomePageAction::class => Action\HomePageFactory::class,
                Action\DocsAction::class => Action\DocsFactory::class,
                Action\SmsAction::class => Action\SmsFactory::class,
                Action\ApiAction::class => Action\ApiActionFactory::class,
                Action\ConfigAction::class => Action\ConfigActionFactory::class,
                Action\SwaggerAction::class => Action\SwaggerFactory::class,
                Action\ToolAction::class => Action\ToolFactory::class,
                Middleware\ApiMiddleware::class => Middleware\ApiMiddlewareFactory::class,
                Middleware\AuthenticationMiddleware::class => Middleware\AuthenticationMiddlewareFactory::class,
                Action\LoginPageAction::class => Action\LoginPageFactory::class,
                Repository\UserTableAuthentication::class => Repository\UserAuthenticationFactory::class,

                Service\Passport::class => Service\PassportFactory::class,
                Service\Payment::class => Service\PaymentFactory::class,
                Mail\MailService::class => Mail\MailServiceFactory::class,
                'PredisCache' => Cache\PredisCacheFactory::class
            ],
            'aliases' => [
                'PassportService' => Service\Passport::class,
                'PaymentService'  => Service\Payment::class,
                'mailService'     => Mail\MailService::class,
                Repository\UserAuthenticationInterface::class => Repository\UserTableAuthentication::class
            ]
        ];
    }

    /**
     * Returns the templates configuration
     *
     * @return array
     */
    public function getTemplates()
    {
        return [
            'paths' => [
                'app'    => [__DIR__ . '/../templates/app'],
                'error'  => [__DIR__ . '/../templates/error'],
                'layout' => [__DIR__ . '/../templates/layout'],
            ],
        ];
    }
}

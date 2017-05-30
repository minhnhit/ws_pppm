<?php

namespace OAuth2Server;

/**
 * The configuration provider for the OAuth2Server module
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
//             'templates'    => $this->getTemplates(),
        	'oauth2' => [
        		'certificates' => [
        			'public' => 'file://' . __DIR__ . '/../../../public.key',
        			'private' => 'file://' . __DIR__ . '/../../../private.key',
        		],
        		'grants' => [
        			'password'           => \League\OAuth2\Server\Grant\PasswordGrant::class,
        			'authorization_code' => \League\OAuth2\Server\Grant\AuthCodeGrant::class,
        			'refresh_token'      => \League\OAuth2\Server\Grant\RefreshTokenGrant::class,
        			'client_credentials' => \League\OAuth2\Server\Grant\ClientCredentialsGrant::class,
        		],
        	],
            'doctrine'     => [
                'driver' => [
                    'orm_default' => [
                        'class' => \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain::class,
                        'drivers' => [
                            'OAuth2Server\Entity' => 'o2s_entity',
                         ],
                    ],
                    'o2s_entity' => [
                            'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                            'cache' => 'array',
                            'paths' => [
                                __DIR__ . '/Entity',
                            ]
                    ],
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
            ],
            'factories'  => [
            		\League\OAuth2\Server\ResourceServer::class => \OAuth2Server\Server\ResourceServerFactory::class,
            		\League\OAuth2\Server\Middleware\ResourceServerMiddleware::class => \OAuth2Server\Middleware\ResourceServerMiddlewareFactory::class,
            		\League\OAuth2\Server\AuthorizationServer::class => \OAuth2Server\Server\AuthorizationServerFactory::class,
            		
            		\OAuth2Server\Action\AuthorizeAction::class => \OAuth2Server\Action\AuthorizeActionFactory::class,
            		\OAuth2Server\Action\AccessTokenAction::class => \OAuth2Server\Action\AccessTokenActionFactory::class,
            		
            		\League\OAuth2\Server\Grant\ClientCredentialsGrant::class => \OAuth2Server\Grant\ClientCredentialsGrantFactory::class,
            		\League\OAuth2\Server\Grant\AuthCodeGrant::class => \OAuth2Server\Grant\AuthCodeGrantFactory::class,
            		\League\OAuth2\Server\Grant\PasswordGrant::class => \OAuth2Server\Grant\PasswordGrantFactory::class,
            		\League\OAuth2\Server\Grant\RefreshTokenGrant::class => \OAuth2Server\Grant\RefreshTokenGrantFactory::class,
            ],
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

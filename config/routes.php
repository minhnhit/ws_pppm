<?php
/**
 * Setup routes with a single request method:
 *
 * $app->get('/', App\Action\HomePageAction::class, 'home');
 * $app->post('/album', App\Action\AlbumCreateAction::class, 'album.create');
 * $app->put('/album/:id', App\Action\AlbumUpdateAction::class, 'album.put');
 * $app->patch('/album/:id', App\Action\AlbumUpdateAction::class, 'album.patch');
 * $app->delete('/album/:id', App\Action\AlbumDeleteAction::class, 'album.delete');
 *
 * Or with multiple request methods:
 *
 * $app->route('/contact', App\Action\ContactAction::class, ['GET', 'POST', ...], 'contact');
 *
 * Or handling all request methods:
 *
 * $app->route('/contact', App\Action\ContactAction::class)->setName('contact');
 *
 * or:
 *
 * $app->route(
 *     '/contact',
 *     App\Action\ContactAction::class,
 *     Zend\Expressive\Router\Route::HTTP_METHOD_ANY,
 *     'contact'
 * );
 */

$app->get('/', App\Action\HomePageAction::class, 'home');
$app->get('/api/ping', App\Action\PingAction::class, 'api.ping');

$app->get('/docs', App\Action\DocsAction::class, 'docs');
$app->get('/tool', App\Action\ToolAction::class, 'tool');
$app->get('/tool/api', App\Action\SwaggerAction::class, 'swagger');
$app->post('/api/{action}', App\Action\ApiAction::class, 'api');
$app->post('/generate/{action}', App\Action\GenAction::class, 'tool.generate');
$app->get('/login', App\Action\LoginPageAction::class, 'login');
$app->get('/{configType:passport|payment}/{action:get-config}', App\Action\ConfigAction::class, 'getConfig');

$app->post('/token/renew', App\Action\TokenAction::class, 'renew.token');
$app->route('/sms/{provider:1pay|fibo}', App\Action\SmsAction::class, ['GET', 'POST'], 'sms');
$app->post('/v{version:\d+}/{action}', App\Action\ApiAction::class, 'api.version');

// OAuth2Server
$app->get('/authorize', \OAuth2Server\Action\AuthorizeAction::class, 'authorize');
$app->post('/access_token', \OAuth2Server\Action\AccessTokenAction::class, 'accessToken');

// Migrate Data
//$app->get('/migrate', App\Action\MigrateAction::class, 'migration.data');

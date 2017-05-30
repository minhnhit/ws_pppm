<?php
namespace App\Middleware;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Router\RouterInterface;

class JWTokenMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $router   = $container->get(RouterInterface::class);
        $config = $container->get('config');
        $passportMapper = $container->get('passportMapper');
        return new JWTokenMiddleware($router, $passportMapper, $config);
    }
}

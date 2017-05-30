<?php
namespace App\Middleware;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Router\RouterInterface;

class ApiMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $router   = $container->get(RouterInterface::class);
        $config   = $container->get('config');
        return new ApiMiddleware($router, $config);
    }
}

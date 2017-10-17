<?php

namespace App\Action;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Router\RouterInterface;

class SmsFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $router   = $container->get(RouterInterface::class);
        $paymentService = $container->get('PaymentService');
        $passportService = $container->get('PassportService');
        return new SmsAction($router, $paymentService, $passportService);
    }
}

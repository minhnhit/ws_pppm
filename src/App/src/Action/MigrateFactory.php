<?php

namespace App\Action;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Router\RouterInterface;

class MigrateFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $sqlDb = $container->get('passport_db');
        $sqlPayDb = $container->get('payment_db');
        $mongoDb = $container->get('PassportService');
        $mongoPayDb = $container->get('PaymentService');
        return new MigrateAction($sqlDb,$sqlPayDb, $mongoDb, $mongoPayDb);
    }
}

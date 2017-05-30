<?php

namespace App\Service;

use Interop\Container\ContainerInterface;

class PaymentFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new Payment($container);
    }
}

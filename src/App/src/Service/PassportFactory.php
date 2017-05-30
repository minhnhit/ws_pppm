<?php

namespace App\Service;

use Interop\Container\ContainerInterface;

class PassportFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new Passport($container);
    }
}

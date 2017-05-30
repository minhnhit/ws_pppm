<?php
/**
 * Created by PhpStorm.
 * User: anhhv
 * Date: 4/19/2017
 * Time: 4:54 PM
 */
namespace App\Mail;

use Interop\Container\ContainerInterface;

class MailServiceFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        return new MailService($config);
    }
}
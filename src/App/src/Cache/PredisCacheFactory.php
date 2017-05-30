<?php
namespace App\Cache;

use Interop\Container\ContainerInterface;

class PredisCacheFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        try {
        	return new \Predis\Client($config['predis_servers']);
        }catch(\Exception $e) {
        	var_dump($e->getMessage());die;
        }
    }
}

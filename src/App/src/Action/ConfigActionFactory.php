<?php
namespace App\Action;

use Interop\Container\ContainerInterface;

class ConfigActionFactory
{
	public function __invoke(ContainerInterface $container)
	{
		$config = $container->get('config');
		return new ConfigAction($container, $config);
	}
}

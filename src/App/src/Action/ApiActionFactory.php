<?php
namespace App\Action;

use Interop\Container\ContainerInterface;

class ApiActionFactory
{
	public function __invoke(ContainerInterface $container)
	{
		return new ApiAction($container);
	}
}

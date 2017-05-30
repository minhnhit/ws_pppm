<?php
namespace App\Container;
use Doctrine\Common\Cache\PredisCache;
use Interop\Container\ContainerInterface;
class DoctrinePredisCacheFactory
{
	public function __invoke(ContainerInterface $container)
	{
		$client = new \Predis\Client();
		return new PredisCache($predis);
	}
}
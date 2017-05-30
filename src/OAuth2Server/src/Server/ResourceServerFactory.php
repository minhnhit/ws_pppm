<?php
namespace OAuth2Server\Server;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use League\OAuth2\Server\ResourceServer;
use OAuth2Server\Entity\AccessToken;

class ResourceServerFactory
{
	public function __invoke(ContainerInterface $container)
	{
		$config = $container->get('config');
		
		return new ResourceServer(
				$clientRepository = $container->get(EntityManager::class)
				->getRepository(AccessToken::class),
				$config['oauth2']['certificates']['public']
			);
	}
}
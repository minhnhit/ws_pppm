<?php
namespace OAuth2Server\Grant;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use OAuth2Server\Entity\RefreshToken;

class RefreshTokenGrantFactory
{
	public function __invoke(ContainerInterface $container)
	{
		$refreshTokenRepository = $container->get(EntityManager::class)->getRepository(RefreshToken::class);
		
		$grant = new RefreshTokenGrant($refreshTokenRepository);
		$grant->setRefreshTokenTTL(new \DateInterval('P1M')); // The refresh token will expire in 1 month
		
		return $grant;
	}
}
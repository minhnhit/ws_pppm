<?php
namespace OAuth2Server\Grant;

use Interop\Container\ContainerInterface;
use League\OAuth2\Server\Grant\ImplicitGrant;

class ImplicitGrantFactory
{
	public function __invoke(ContainerInterface $container)
	{
		return new ImplicitGrant(
				new \DateInterval('PT1H')
			);
	}
}
<?php
namespace OAuth2Server\Grant;

use Interop\Container\ContainerInterface;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;

class ClientCredentialsGrantFactory
{
	public function __invoke(ContainerInterface $container)
	{
		return new ClientCredentialsGrant();
	}
}
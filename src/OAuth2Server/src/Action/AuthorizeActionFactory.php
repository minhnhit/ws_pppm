<?php
namespace OAuth2Server\Action;

use Interop\Container\ContainerInterface;

class AuthorizeActionFactory
{
	/**
	 * @param ContainerInterface $container
	 * @param string $requestedName
	 * @param null|array $options
	 * @return AuthorizeAction
	 */
	public function __invoke(ContainerInterface $container)
	{
		return new AuthorizeAction($container->get(\League\OAuth2\Server\AuthorizationServer::class));
	}
}

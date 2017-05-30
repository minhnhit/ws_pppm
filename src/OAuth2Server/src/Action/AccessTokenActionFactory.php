<?php
namespace OAuth2Server\Action;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use OAuth2Server\Action\AccessTokenAction;

class AccessTokenActionFactory implements FactoryInterface
{
	/**
	 * @param ContainerInterface $container
	 * @param string $requestedName
	 * @param null|array $options
	 * @return AccessTokenAction
	 */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		return new AccessTokenAction($container->get(\League\OAuth2\Server\AuthorizationServer::class));
	}
}

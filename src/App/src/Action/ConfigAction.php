<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Container\ContainerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use App\Middleware\ApiMiddleware;
use Zend\Expressive\Router\RouterInterface;

class ConfigAction implements MiddlewareInterface
{
    private $container;

	private $config;
	
	public function __construct($container, $config)
	{
	    $this->container = $container;
		$this->config = $config;
	}
	
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $postData = $request->getParsedBody();
        $queryData = $request->getQueryParams();
        $data = array_merge($postData, $queryData);
        if (!isset($data['client_id'])
            || !isset($this->config['partner'][strtolower($data['client_id'])])) {
            return new JsonResponse(['code' => -2]); // clientId not found
        }

    	$router = $this->container->get(RouterInterface::class);
    	$routeMatch = $router->match($request);
    	$matchedParams = $routeMatch->getMatchedParams();
    	$method_name= $matchedParams['action'];
    	$version = isset($matchedParams['version']) ? $matchedParams['version'] : 1;
        $configArr = $this->config[$matchedParams['action']][$matchedParams['configType']];
        return new JsonResponse($configArr);
    }
}

<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Container\ContainerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use App\Middleware\ApiMiddleware;
use Zend\Expressive\Router\RouterInterface;

class ApiAction implements MiddlewareInterface
{
	private $container;
	
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}
	
	/**
	 * @SWG\Post(
	 *     path="/api/{action}",
	 *     description="API Action",
	 *     operationId="process",
	 *     produces={"application/json"},
	 *     tags={"API"},
	 *     @SWG\Parameter(
     *         name="action",
     *         in="path",
     *         description="action",
     *         required=true,
     *         type="string",
     *         @SWG\Items(type="string"),
     *         collectionFormat="csv",
     *         enum={"login","register","oauth","reset-pass","forgot-pass","change-pass","update-email", "get-email",
     *         "update-mobile", "charge", "exchange","get-balance","buy-card","update-match","promotion","recheck",
     *         "update-username"
     *     }
     *     ),
	 *     @SWG\Parameter(
	 *         name="client_id",
	 *         in="formData",
	 *         description="tags to filter by",
	 *         required=true,
	 *         type="string",
	 *         @SWG\Items(type="string"),
	 *         collectionFormat="csv"
	 *     ),
	 *     @SWG\Parameter(
	 *         name="app_key",
	 *         in="formData",
	 *         description="Random key with RSA encrypted",
	 *         required=true,
	 *         type="string",
	 *     ),
	 *     @SWG\Parameter(
	 *         name="data",
	 *         in="formData",
	 *         description="Data Encrypted with AES",
	 *         required=true,
	 *         type="string",
	 *     ),
	 *     @SWG\Response(
	 *         response=200,
	 *         description="user info response",
	 *         @SWG\Schema(
	 *             type="json",
	 *             @SWG\Items(ref="#/definitions/login")
	 *         ),
	 *     ),
	 *     @SWG\Response(
	 *         response="default",
	 *         description="unexpected error",
	 *         @SWG\Schema(
	 *             ref="#/definitions/Error"
	 *         )
	 *     )
	 * )
	 */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
    	$router = $this->container->get(RouterInterface::class);
    	$routeMatch = $router->match($request);
    	$matchedParams = $routeMatch->getMatchedParams();
    	$method_name= strtolower($matchedParams['action']);
    	$version = isset($matchedParams['version']) ? $matchedParams['version'] : null;
    	
    	$serviceName= "PassportService";
    	if(in_array($method_name, ['charge', 'exchange', 'get-balance', 'update-match','buy-card', 'promotion', 'recheck'])) {
    		$serviceName = "PaymentService";
    	}
    	
    	// match method here
    	$filter = new \Zend\Filter\Word\DashToCamelCase();
    	$method_name = lcfirst($filter->filter($method_name));
    	
    	$obj = $this->container->get($serviceName);
    	if(method_exists($obj, $method_name)) {
	    	$data = $request->getAttribute(ApiMiddleware::class);
	    	$clientID = $data['client_id'];
	    	$result = $obj->{$method_name}($data);
	    	if ($result['code'] == 1 && isset($result['result'])) {
	    		$pass = generateRandomString(16);
	    		$pass_phrase = isset($this->config['partner'][$clientID]['password']) ?
	    		$this->config['partner'][$clientID]['password'] : null;
	    		$result['app_key'] = \Util::encryptRsa($clientID, $pass, $pass_phrase);
	    		$result['result'] = \Util::encryptAes($pass, json_encode($result['result']));
	    	}
    	}else {
    		$result = ['code' => -9999];
    	}
    	
    	return new JsonResponse($result);
    }
}

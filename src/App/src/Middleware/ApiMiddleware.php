<?php
namespace App\Middleware;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Router;
use Zend\Diactoros\Response\JsonResponse;

class ApiMiddleware implements MiddlewareInterface
{
    /**
     * @var Router\RouterInterface
     */
    private $router;
    
    private $config;
    
    /**
     * AuthenticationMiddleware constructor
     *
     * @param Router\RouterInterface $router
     * @param array $template
     */
    public function __construct(
        Router\RouterInterface $router, $config
    ) {
        $this->router = $router;
        $this->config = $config;
    }
    
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
    	$postData = $request->getParsedBody();
    	$queryData = $request->getQueryParams();
    	$data = array_merge($postData, $queryData);
    	
    	if (!isset($data['client_id'])) {
    		return new JsonResponse(['code' => -2]); // clientId not found
    	}
    	
    	if (!isset($data['app_key'])) {
    		return new JsonResponse(['code' => -3]); // app key not found
    	}
    	
    	$encryptedData = isset($data['data'])? $data['data'] : '';
    	unset($data['data']);
    	$clientID = strtolower($data['client_id']);
    	$pass_phrase = isset($this->config['partner'][$clientID]['password']) ?
    	$this->config['partner'][$clientID]['password']: null;
    	$appKey = \Util::decryptRsa($clientID, $data['app_key'], $pass_phrase);
    	if (!$appKey) {
    		return new JsonResponse(['code' => -4]);
    	} else {
    		unset($data['app_key']);
    		$result = \Util::decryptAes($appKey, $encryptedData);
    		if (!$result) {
    			return new JsonResponse(['code' => -4]);
    		}
    		
    		if (is_string($result) && is_array(json_decode($result, true))) {
    			$data = array_merge($data, json_decode($result, true));
    		} else {
    			return new JsonResponse(['code' => -4]);
    		}
    	}
    	$response = $delegate->process($request->withAttribute(self::class, $data));
    	return $response;
    }
}

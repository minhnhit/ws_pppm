<?php

namespace OAuth2Server\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Stream;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;

class AccessTokenAction implements MiddlewareInterface
{
	/**
	 * @var \League\OAuth2\Server\AuthorizationServer
	 */
	protected $server = null;
	
	public function __construct(AuthorizationServer $server)
	{
		$this->server = $server;
	}
	
    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
    	$response = $delegate->process($request);
    	try {
    		return $this->server->respondToAccessTokenRequest($request, $response);
    	} catch (OAuthServerException $exception) {
    		return $exception->generateHttpResponse($response);
    	} catch (\Exception $exception) {
    		$body = new Stream('php://temp', 'r+');
    		$body->write($exception->getMessage());
    		
    		return $response->withStatus(500)->withBody($body);
    	}
    	
        return $response;
    }
}
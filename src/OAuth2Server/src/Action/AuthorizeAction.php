<?php

namespace OAuth2Server\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Zend\Diactoros\Stream;

class AuthorizeAction implements MiddlewareInterface
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
        $data = $request->getQueryParams();
        try {
        	// Validate the HTTP request and return an AuthorizationRequest object.
        	// The auth request object can be serialized into a user's session
        	$authRequest = $this->server->validateAuthorizationRequest($request);
        	
        	// Once the user has logged in set the user on the AuthorizationRequest
        	$authRequest->setUser(new \OAuth2Server\Entity\User());
        	
        	// Once the user has approved or denied the client update the status
        	// (true = approved, false = denied)
        	$authRequest->setAuthorizationApproved(true);
        	
        	// Return the HTTP redirect response
        	return $this->server->completeAuthorizationRequest($authRequest, $response);
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
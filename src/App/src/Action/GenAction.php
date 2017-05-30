<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class GenAction implements ServerMiddlewareInterface
{
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
    	$postData = $request->getParsedBody();
    	$queryData = $request->getQueryParams();
    	$data = array_merge($postData, $queryData);
    	$clientId = $data['client_id'];
    	unset($data['client_id']);
    	$pass = generateRandomString(16);
    	$key = RSAEncryptData(strtolower($clientId), $pass);
    	$encryptedData = aes256_encrypt($pass, json_encode($postData));
    	$data = ['client_id' => $clientId, 'app_key' => $key, 'data' => $encryptedData];
    	return new JsonResponse($data);
    }
}

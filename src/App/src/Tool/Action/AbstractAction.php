<?php
namespace App\Tool\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

abstract class AbstractAction implements ServerMiddlewareInterface
{
	public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $postData = $request->getParsedBody();
        $clientId = $postData['client_id'];
        unset($postData['client_id']);
        $pass = generateRandomString(16);
        $key = RSAEncryptData(strtolower($clientId), $pass);
        $encryptedData = aes256_encrypt($pass, json_encode($postData));
        $data = ['client_id' => $clientId, 'app_key' => $key, 'data' => $encryptedData];
        return new JsonResponse($data);
    }
}

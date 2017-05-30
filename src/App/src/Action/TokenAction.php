<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use App\Middleware\ApiMiddleware;

class TokenAction implements ServerMiddlewareInterface
{
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
    	$data = $request->getAttribute(ApiMiddleware::class);
    	var_dump($data);die;
        return new JsonResponse(['ack' => time()]);
    }
}

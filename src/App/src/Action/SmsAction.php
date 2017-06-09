<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Router;

/**
 * @SWG\Post(
 *     path="/sms/{provider}",
 *     description="API SMS",
 *     operationId="process",
 *     produces={"application/json"},
 *     tags={"API"},
 *     @SWG\Parameter(
 *         name="provider",
 *         in="path",
 *         description="provider",
 *         required=true,
 *         type="string",
 *         @SWG\Items(type="string"),
 *         collectionFormat="csv",
 *         enum={"1pay","fibo"}
 *     ),
 *     @SWG\Parameter(
 *         name="params",
 *         in="path",
 *         description="tags to filter by",
 *         required=false,
 *         type="string",
 *         @SWG\Items(type="string"),
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
class SmsAction implements ServerMiddlewareInterface
{
    private $router;

    private $paymentService;

    public function __construct(Router\RouterInterface $router, $paymentService)
    {
        $this->router   = $router;
        $this->paymentService = $paymentService;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $postData = $request->getParsedBody();
        $queryData = $request->getQueryParams();
        $data = array_merge($postData, $queryData);

        $routeMatchParams = $this->router->match($request)->getMatchedParams();
        $provider = $routeMatchParams['provider'];
        $result = $this->paymentService->chargeSMS($data, $provider);
		$result['sms'] .= " Lien he <a href='https://www.facebook.com/coupviet'>https://www.facebook.com/coupviet</a> de biet them chi tiet";
        return new JsonResponse($result);
    }
}

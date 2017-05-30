<?php
namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @SWG\Swagger(
 *     @SWG\Info(
 *         version="2.0",
 *         title="API Documentation",
 *         @SWG\License(name="MIT")
 *     ),
 *     basePath="/",
 *     schemes={"http","https"},
 *     consumes={"application/json"},
 *     produces={"application/json"},
 *     @SWG\Definition(
 *         definition="Error",
 *         required={"code", "msg", "result"},
 *         @SWG\Property(
 *             property="code",
 *             type="integer",
 *             format="int32"
 *         ),
 *         @SWG\Property(
 *             property="msg",
 *             type="string"
 *         ),
 *         @SWG\Property(
 *             property="result",
 *             type="string"
 *         )
 *     ),
 *     @SWG\Definition(
 *         definition="ClientList",
 *         type="string",
 *         enum={"code", "msg", "result"},
 *         collectionFormat="csv"
 *     )
 * )
 */
class SwaggerAction implements MiddlewareInterface
{
    private $swagger;

    public function __construct($swagger)
    {
        $this->swagger = $swagger;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return new JsonResponse((array)$this->swagger->jsonSerialize());
    }
}

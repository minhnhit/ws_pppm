<?php

namespace App\Tool\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ExchangeAction extends AbstractAction
{
    /**
     * @SWG\Post(
     *     path="/generate/exchange",
     *     description="Generate exchange data",
     *     operationId="__invoke",
     *     produces={"application/json"},
     *     tags={"Payment Generation Data"},
     *     @SWG\Parameter(
     *         name="client_id",
     *         in="formData",
     *         description="tags to filter by",
     *         required=true,
     *         type="string",
     *         @SWG\Items(type="string"),
     *         collectionFormat="csv",
     *         enum={"b1","c1", "ttkn"}
     *     ),
     *     @SWG\Parameter(
     *         name="passportId",
     *         in="formData",
     *         description="User ID",
     *         required=true,
     *         type="integer",
     *         default=""
     *     ),
     *     @SWG\Parameter(
     *         name="amount",
     *         in="formData",
     *         description="Gold to exchange",
     *         required=true,
     *         type="integer",
     *         default=""
     *     ),
     *     @SWG\Parameter(
     *         name="serverId",
     *         in="formData",
     *         description="Server ID to exchange",
     *         required=true,
     *         type="string",
     *         default=""
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
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        return parent::__invoke($request, $response, $next);
    }
}

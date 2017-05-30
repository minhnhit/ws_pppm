<?php

namespace App\Tool\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class BalanceAction extends AbstractAction
{
    /**
     * @SWG\Post(
     *     path="/generate/get-balance",
     *     description="Generate get balance data",
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
     *         required=false,
     *         type="integer",
     *         default=""
     *     ),
     *     @SWG\Parameter(
     *         name="username",
     *         in="formData",
     *         description="Username",
     *         required=false,
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

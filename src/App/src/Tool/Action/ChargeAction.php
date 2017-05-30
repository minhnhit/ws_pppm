<?php

namespace App\Tool\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ChargeAction extends AbstractAction
{
    /**
     * @SWG\Post(
     *     path="/generate/charge",
     *     description="Generate charge card data",
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
     *         name="cardNumber",
     *         in="formData",
     *         description="Card Pin (Number)",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="cardSerial",
     *         in="formData",
     *         description="Card Serial",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="cardType",
     *         in="formData",
     *         description="Card type: VINA | MOBI | VT",
     *         required=true,
     *         type="string",
     *         enum={"VINA","MOBI","VT"},
     *         default="",
     *     ),
     *     @SWG\Parameter(
     *         name="agentTransactionId",
     *         in="formData",
     *         description="Only use service charge",
     *         required=false,
     *         type="string",
     *         default=""
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
     *     @SWG\Parameter(
     *         name="gold",
     *         in="formData",
     *         description="Gold to exchange",
     *         required=false,
     *         type="integer",
     *         default=""
     *     ),
     *     @SWG\Parameter(
     *         name="serverId",
     *         in="formData",
     *         description="Server ID to exchange",
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

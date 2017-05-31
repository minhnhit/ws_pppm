<?php

namespace App\Tool\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PromotionAction extends AbstractAction
{
    /**
     * @SWG\Post(
     *     path="/generate/promotion",
     *     description="Generate promotion data",
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
     *         name="username",
     *         in="formData",
     *         description="Username",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="promotionId",
     *         in="formData",
     *         description="Promotion ID",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="promotionCode",
     *         in="formData",
     *         description="Promotion Code",
     *         required=true,
     *         type="string",
     *         default="",
     *     ),
     *     @SWG\Parameter(
     *         name="gold",
     *         in="formData",
     *         description="Gold",
     *         required=true,
     *         type="integer",
     *         default="silver",
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

<?php

namespace App\Tool\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MatchAction extends AbstractAction
{
    /**
     * @SWG\Post(
     *     path="/generate/match",
     *     description="Generate match data",
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
     *         name="winner_username",
     *         in="formData",
     *         description="Winner Username",
     *         required=true,
     *         type="string",
     *         default=""
     *     ),
     *     @SWG\Parameter(
     *         name="loser_username",
     *         in="formData",
     *         description="Loser user",
     *         required=true,
     *         type="string",
     *         default=""
     *     ),
     *     @SWG\Parameter(
     *         name="matchId",
     *         in="formData",
     *         description="Match ID",
     *         required=true,
     *         type="string",
     *         default=""
     *     ),
     *     @SWG\Parameter(
     *         name="gold",
     *         in="formData",
     *         description="Gold",
     *         required=true,
     *         type="integer",
     *         default=""
     *     ),
     *     @SWG\Parameter(
     *         name="status",
     *         in="formData",
     *         description="Match status",
     *         required=true,
     *         type="integer",
     *         default="1"
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

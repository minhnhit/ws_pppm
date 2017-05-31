<?php

namespace App\Tool\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RegisterAction extends AbstractAction
{
    /**
     * @SWG\Post(
     *     path="/generate/register",
     *     description="Generate register data",
     *     operationId="__invoke",
     *     produces={"application/json"},
     *     tags={"Passport Generation Data"},
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
     *         name="password",
     *         in="formData",
     *         description="Password",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="source",
     *         in="formData",
     *         description="Source",
     *         required=false,
     *         type="string",
     *         default="568E"
     *     ),
     *     @SWG\Parameter(
     *         name="agent_id",
     *         in="formData",
     *         description="Agent",
     *         required=false,
     *         type="string",
     *         default=""
     *     ),
     *     @SWG\Parameter(
     *         name="email",
     *         in="formData",
     *         description="Email",
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

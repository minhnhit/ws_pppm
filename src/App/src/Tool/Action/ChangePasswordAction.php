<?php

namespace App\Tool\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ChangePasswordAction extends AbstractAction
{
    /**
     * @SWG\Post(
     *     path="/generate/change-pass",
     *     description="Generate change password data",
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
     *         name="oldPassword",
     *         in="formData",
     *         description="Old Password",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="newPassword",
     *         in="formData",
     *         description="newPassword",
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

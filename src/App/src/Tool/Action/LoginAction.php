<?php

namespace App\Tool\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoginAction extends AbstractAction
{
    /**
     * @SWG\Post(
     *     path="/generate/login",
     *     description="Generate login data",
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
	public function process(ServerRequestInterface $request, DelegateInterface $delegate)
	{
		$data = $request->getAttribute(ApiMiddleware::class);
		var_dump($data);die;
		return new JsonResponse(['ack' => time()]);
	}
}

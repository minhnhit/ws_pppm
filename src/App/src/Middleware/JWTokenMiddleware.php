<?php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Firebase\JWT\JWT;

class JWTokenMiddleware
{
    /**
     * @var array
     */
    protected $router;
    /**
     * @param array $config
     */
    protected $config;

    protected $passportMapper;

    public function __construct($router, $passportMapper, $config)
    {
        $this->router = $router;
        $this->config = $config;
        $this->passportMapper = $passportMapper;
    }
    /**
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $data = $request->getAttribute('data');
        $routeMatch = $this->router->match($request);
        $matchedParams = $routeMatch->getMatchedParams();
        $action  = isset($matchedParams['action'])? $matchedParams['action'] : null;
        if(!in_array($action, ['login', 'register', 'oauth'])) {
            $authHeader = $request->getHeaderLine('authorization');
            if($authHeader) {
                list($jwt) = sscanf( $authHeader, 'Bearer %s');
                if($jwt) {
                    $secret = env("JWT_SECRET", openssl_random_pseudo_bytes(64));
                    try {
                        $token = JWT::decode($jwt, $secret, env("JWT_HASH", "HS256"));
                        $result = json_decode($token, true);
                        $data['passportId'] = $result['uid'];
                        $data['username'] = $result['identity'];

                        $userLogged = $this->passportMapper->getById($data['passportId']);
                        if ($userLogged->getPassword() !== $result['credential']) {
                            return $response->withStatus(401, 'Unauthorized')
                                ->getBody()->write('401 Unauthorized');
                        }

                    } catch (\Exception $e) {
                        return $response->withStatus(401, $e->getMessage())
                            ->getBody()->write('401 ' . $e->getMessage());
                    }
                }else {
                    return $response->withStatus(400, 'Bad Request')
                            ->getBody()->write('400 Bad Request');
                }
            }else {
                return $response->withStatus(400, 'Bad Request')
                        ->getBody()->write('400 Bad Request');
            }
        }

        return $next(
                $request->withAttribute('data', $data),
                $response
            );
    }
}

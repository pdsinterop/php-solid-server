<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse as JsonResponse;

class TokenController extends ServerController
{    
    final public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {	
		$code = $request->getParsedBody()['code'];
		$clientId = $request->getParsedBody()['client_id'];

/*
		$DPoP = $_SERVER['HTTP_DPOP'];
		$parser = new \Lcobucci\JWT\Parser();
		try {
			$token = $parser->parse($DPoP);
//			var_dump($token);
		} catch(\Exception $e) {
			return $this->getResponse()->withStatus(409, "Invalid token");
		}
*/
		$response = new \Laminas\Diactoros\Response();
		$server	= new \Pdsinterop\Solid\Auth\Server($this->authServerFactory, $this->authServerConfig, $response);
		$response = $server->respondToAccessTokenRequest($request);

		$codeInfo = $this->tokenGenerator->getCodeInfo($code);
		$response = $this->tokenGenerator->addIdTokenToResponse($response, $clientId, $codeInfo['user_id'], $_SESSION['nonce'], $this->config->getPrivateKey());

		return $response;
    }
}

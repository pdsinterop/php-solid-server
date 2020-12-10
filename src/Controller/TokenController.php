<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse as JsonResponse;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class TokenController extends ServerController
{    
    final public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {	
		$code = $request->getParsedBody()['code'];
		$clientId = $request->getParsedBody()['client_id'];

		$dpop = $request->getServerParams()['HTTP_DPOP'];
		if ($dpop) {
			try {
				$dpopKey = $this->getDpopKey($dpop, $request);
				error_log("dpop looks valid!");
			} catch(\Exception $e) {
				error_log("invalid!");
				return $this->getResponse()->withStatus(409, "Invalid token");
			}
		}
		
		$response = new \Laminas\Diactoros\Response();
		$server	= new \Pdsinterop\Solid\Auth\Server($this->authServerFactory, $this->authServerConfig, $response);
		$response = $server->respondToAccessTokenRequest($request);

		// FIXME: not sure if decoding this here is the way to go.
		// FIXME: because this is a public page, the nonce from the session is not available here.
		$codeInfo = $this->tokenGenerator->getCodeInfo($code);
		$response = $this->tokenGenerator->addIdTokenToResponse($response, $clientId, $codeInfo['user_id'], $_SESSION['nonce'], $this->config->getPrivateKey(), $dpopKey);

		return $response;
    }
}

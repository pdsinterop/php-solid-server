<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse as JsonResponse;

class TokenController extends ServerController
{    
    final public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {	
		$code = $_POST['code'];
		$clientId = $_POST['client_id'];
		$DPoP = $_SERVER['HTTP_DPOP'];
		
		$parser = new \Lcobucci\JWT\Parser();
		try {
			$token = $parser->parse($DPoP);
//			var_dump($token);
		} catch(\Exception $e) {
			return $this->getResponse()->withStatus(409, "Invalid token");
		}
		
		$registration = $this->config->getClientRegistration($clientId);
		$approval = $this->checkApproval($clientId);
		
		if ($approval) {
			$response = new \Laminas\Diactoros\Response();
			$server	= new \Pdsinterop\Solid\Auth\Server($this->authServerFactory, $this->authServerConfig, $response);
			$response = $server->respondToAccessTokenRequest($request);

//			$response = $this->tokenGenerator->addIdTokenToResponse($response, $clientId, $this->getProfilePage(), $_SESSION['nonce'], $this->config->getPrivateKey());
			return $response;
//			$idToken = $this->tokenGenerator->generateIdToken($code, $clientId, $this->getProfilePage(), $_SESSION['nonce'], $this->config->getPrivateKey());
	//		return new JsonResponse(array("token_type" => "DPoP", "id_token" => $idToken));
		}
		return new JsonResponse(array());
    }
}

<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse as JsonResponse;

class RegisterController extends ServerController
{    
    final public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {	
		$clientData = file_get_contents('php://input');
		$clientData = json_decode($clientData, true);
		if (!$clientData['redirect_uris']) {
			return $response->withStatus(400, "Missing redirect URIs");
		}
		$clientData['client_id_issued_at'] = time();
		$parsedOrigin = parse_url($clientData['redirect_uris'][0]);
		$origin = $parsedOrigin['host'];

		$clientId = $this->config->saveClientRegistration($origin, $clientData);
		
		$registration = array(
			'client_id' => $clientId,
			'registration_client_uri' => "https://localhost/clients/$clientId", // FIXME: properly generate this url;
			'client_id_issued_at' => $clientData['client_id_issued_at'],
			'redirect_uris' => $clientData['redirect_uris'],
		);
		
		$registration = $this->tokenGenerator->respondToRegistration($registration, $this->config->getPrivateKey());
		header("Access-Control-Allow-Origin: *");
		return new JsonResponse($registration);
    }
}

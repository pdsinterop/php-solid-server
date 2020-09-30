<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthorizeController extends ServerController
{
    final public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
		$parser = new \Lcobucci\JWT\Parser();

		try {
			$token = $parser->parse($_GET['request']);
			$_SESSION["nonce"] = $token->getClaim('nonce');
		} catch(\Exception $e) {
			$_SESSION["nonce"] = $_GET['nonce'];
		}

		$getVars = $_GET;
		if (!isset($getVars['grant_type'])) {
			$getVars['grant_type'] = 'implicit';
		}
		$getVars['response_type'] = $this->getResponseType();
		$getVars['scope'] = "openid" ;

		if (!isset($getVars['redirect_uri'])) {
			try {
				$getVars['redirect_uri'] = $token->getClaim("redirect_uri");
			} catch(\Exception $e) {
				$response = $this->getResponse();
				$response->withStatus(400, "Bad request, missing redirect uri");
				return $response;
			}
		}
		$clientId = $getVars['client_id'];
		$approval = $this->checkApproval($clientId);	
		if (!$approval) {
			$response = $this->getResponse();
			$response = $response->withStatus(302, "Approval required");
			
			// FIXME: Generate a proper url for this;
			$approvalUrl = "http://localhost/approval/?client_id=$clientId&returnUrl=" . urlencode($_SERVER['REQUEST_URI']);
			$response = $response->withHeader("Location", $approvalUrl);
			return $response;
		}

		$user = new \Pdsinterop\Solid\Auth\Entity\User();
		$user->setIdentifier($this->getProfilePage());

		$request = \Laminas\Diactoros\ServerRequestFactory::fromGlobals($_SERVER, $getVars, $_POST, $_COOKIE, $_FILES);
		$response = new \Laminas\Diactoros\Response();
		$server	= new \Pdsinterop\Solid\Auth\Server($this->authServerFactory, $this->authServerConfig, $response);

		$response = $server->respondToAuthorizationRequest($request, $user, $approval);
		$response = $this->tokenGenerator->addIdTokenToResponse($response, $clientId, $this->getProfilePage(), $_SESSION['nonce'], $this->config->getPrivateKey());
		return $response;
	}
}
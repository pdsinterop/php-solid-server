<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;

class AuthorizeController extends ServerController
{
    final public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        if (!isset($_SESSION['userid'])) {
			// FIXME: Generate a proper url for this;
			$loginUrl = $this->baseUrl . "/login/?returnUrl=" . urlencode($_SERVER['REQUEST_URI']);

            return $this->getResponse()
                ->withHeader("Location", $loginUrl)
                ->withStatus(302, "Approval required")
            ;
		}

        $queryParams = $request->getQueryParams();

		$jwtConfig = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText($this->config->getPrivateKey()));

		try {
			$token = $jwtConfig->parser()->parse($request->getQueryParams()['request']);
			$_SESSION["nonce"] = $token->claims()->get('nonce');
		} catch(\Exception $e) {
			$_SESSION["nonce"] = $request->getQueryParams()['nonce'];
		}

        /*/ Prepare GET parameters for OAUTH server request /*/
		$getVars = $queryParams;

		$getVars['response_type'] = $this->getResponseType($queryParams);
		$getVars['scope'] = "openid" ;

		if (!isset($getVars['grant_type'])) {
			$getVars['grant_type'] = 'implicit';
		}

		if (!isset($getVars['redirect_uri'])) {
			try {
				$getVars['redirect_uri'] = $token->claims()->get("redirect_uri");
			} catch(\Exception $e) {
				return $this->getResponse()
                    ->withStatus(400, "Bad request, missing redirect uri")
                ;
			}
		}

        if (! isset($queryParams['client_id'])) {
            return $this->getResponse()
                ->withStatus(400, "Bad request, missing client_id")
            ;
        }

        $clientId = $getVars['client_id'];
		$approval = $this->checkApproval($clientId);
		if (!$approval) {
			// FIXME: Generate a proper url for this;
			$approvalUrl = $this->baseUrl . "/sharing/$clientId/?returnUrl=" . urlencode($_SERVER['REQUEST_URI']);

            return $this->getResponse()
                ->withHeader("Location", $approvalUrl)
                ->withStatus(302, "Approval required")
            ;
		}

        // replace the request getVars with the morphed version
		$request = $request->withQueryParams($getVars);

		$user = new \Pdsinterop\Solid\Auth\Entity\User();
		$user->setIdentifier($this->getProfilePage());

		$response = new \Laminas\Diactoros\Response();
		$server	= new \Pdsinterop\Solid\Auth\Server($this->authServerFactory, $this->authServerConfig, $response);

		$response = $server->respondToAuthorizationRequest($request, $user, $approval);

        return $this->tokenGenerator->addIdTokenToResponse($response,
            $clientId,
            $this->getProfilePage(),
            $_SESSION['nonce'],
            $this->config->getPrivateKey()
        );
	}
}

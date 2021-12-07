<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse as JsonResponse;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Pdsinterop\Solid\Auth\Utils\DPop as DPop;

class TokenController extends ServerController
{    
    final public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
		$code = $request->getParsedBody()['code'];
		$clientId = $request->getParsedBody()['client_id'];
		$DPop = new DPop();
		$dpop = $request->getServerParams()['HTTP_DPOP'];
		try {
			$dpopKey = $DPop->getDPopKey($dpop, $request);
		} catch(\Exception $e) {
			return $this->getResponse()->withStatus(409, "Invalid token");
		}

		$server = new \Pdsinterop\Solid\Auth\Server(
            $this->authServerFactory,
            $this->authServerConfig,
            new \Laminas\Diactoros\Response()
        );

		$response = $server->respondToAccessTokenRequest($request);

		// FIXME: not sure if decoding this here is the way to go.
		// FIXME: because this is a public page, the nonce from the session is not available here.
		$codeInfo = $this->tokenGenerator->getCodeInfo($code);

        return $this->tokenGenerator->addIdTokenToResponse($response,
            $clientId,
            $codeInfo['user_id'],
            $_SESSION['nonce'],
            $this->config->getPrivateKey(),
            $dpopKey
        );
    }
}

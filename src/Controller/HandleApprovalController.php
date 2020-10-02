<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HandleApprovalController extends ServerController
{    
    public function __invoke(ServerRequestInterface $request, array $args) : ResponseInterface
    {
		$clientId = $args['clientId'];
		$returnUrl = $request->getParsedBody()['returnUrl'];
		$approval = $request->getParsedBody()['approval'];

		if ($approval == "allow") {		
			$this->config->addAllowedClient($this->userId, $clientId);
		} else {
			$this->config->removeAllowedClient($this->userId, $clientId);
		}

		$response = $this->getResponse();
		$response = $response->withHeader("Location", $returnUrl);
		$response = $response->withStatus("302", "ok");
		return $response;
    }
}

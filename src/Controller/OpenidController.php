<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class OpenidController extends ServerController
{    
    final public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {	
        $response = $this->getResponse();
		$server	= new \Pdsinterop\Solid\Auth\Server($this->authServerFactory, $this->authServerConfig, $response);
		return $server->respondToOpenIdMetadataRequest();
    }
}

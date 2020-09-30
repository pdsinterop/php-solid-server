<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CorsController extends ServerController
{    
    final public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {	
        $response = $this->getResponse();
		$response = $response->withHeader("Access-Control-Allow-Headers", "*");
		return $response;
    }
}

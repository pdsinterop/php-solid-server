<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CorsController extends ServerController
{    
    final public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {	
		$origin = $request->getServerParams()['HTTP_ORIGIN'];
		return $this->getResponse()->withHeader("Access-Control-Allow-Origin", $origin)
			->withHeader("Access-Control-Allow-Headers", "authorization, content-type")
			->withHeader("Access-Control-Allow-Credentials", "true");
    }
}

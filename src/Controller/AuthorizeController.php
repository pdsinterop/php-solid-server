<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthorizeController extends AbstractController
{
    final public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
				$httpHost = $request->getServerParams()['HTTP_HOST'];
				$response = $this->getResponse();

        $response->getBody()->write("Hello $httpHost");

        return $response;
    }
}

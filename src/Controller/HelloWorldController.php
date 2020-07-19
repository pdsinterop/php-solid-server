<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HelloWorldController extends AbstractController
{
    final public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $response = $this->getResponse();

        $response->getBody()->write('<h1>Hello, World!</h1>');

        return $response;
    }
}

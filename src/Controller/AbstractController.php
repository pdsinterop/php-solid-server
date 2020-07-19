<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractController
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\

    /** @var ResponseInterface */
    private $response;

    //////////////////////////// GETTERS AND SETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\

    final public function getResponse() : ResponseInterface
    {
        return $this->response;
    }

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    abstract public function __invoke(ServerRequestInterface $request, array $args) : ResponseInterface;

    final public function createRedirectResponse(string $url, int $status = 302) : ResponseInterface
    {
        return $this->response->withHeader('location', $url)->withStatus($status);
    }

    final public function createTextResponse(string $message, int $status = 200) : ResponseInterface
    {
        $body = $this->response->getBody();

        $body->write($message);

        return $this->response->withBody($body)->withStatus($status);
    }
}

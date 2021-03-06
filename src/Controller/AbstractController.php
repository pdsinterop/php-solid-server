<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Pdsinterop\Solid\Traits\HasResponseTrait;
use Pdsinterop\Solid\Traits\HasTemplateTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractController
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\

    use HasResponseTrait;
    use HasTemplateTrait;

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    abstract public function __invoke(ServerRequestInterface $request, array $args) : ResponseInterface;

    final public function createRedirectResponse(string $url, int $status = 302) : ResponseInterface
    {
        return $this->getResponse()
            ->withHeader('location', $url)
            ->withStatus($status)
        ;
    }

    final public function createTemplateResponse(string $template, array $context = []) : ResponseInterface
    {
        $response = $this->buildTemplate($template, $context);

        return $this->createTextResponse($response);
    }

    final public function createTextResponse(string $message, int $status = 200) : ResponseInterface
    {
        $response = $this->getResponse();

        $body = $response->getBody();

        $body->write($message);

        return $response->withBody($body)->withStatus($status);
    }
}

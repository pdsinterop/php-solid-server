<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractNotImplementedController extends AbstractController
{
  final public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
  {
    $message = "{$request->getRequestTarget()} has not been implemented yet";

    return $this->createTextResponse($message, 501);
  }
}
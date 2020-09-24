<?php declare(strict_types=1);

namespace Acme\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SomeMiddleware implements MiddlewareInterface
{
  /**
   * {@inheritdoc}
   */
  public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
  {
    // ...
    return $handler->handler($request);
  }
  /**
   * To control whether your logic runs before or after your controller, you can
   * have the request handler run as the first thing you do in your middleware,
   * it will return a response, you can then do whatever you need to with the
   * response and return it.
   * {@inheritdoc}
   */
  public function _process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
  {
    // invoke the rest of the middleware stack and your controller resulting
    // in a returned response object
    $response = $handler->handle($request);

    // ...
    // do something with the response
    return $response;
  }
}

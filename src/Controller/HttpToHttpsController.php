<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpToHttpsController extends AbstractController
{
  final public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
  {
    $serverParams = $request->getServerParams();

    if (isset($serverParams['HTTP_HOST']) === false) {
      $message = 'Could not determine host name.' .
        'The server\'s HTTP_HOST variable has not been set!';

      $response = $this->createTextResponse($message, 500);
    } else {
      $url = 'https://' . $serverParams['HTTP_HOST'] . $request->getRequestTarget();

      $response = $this->createRedirectResponse($url, 301);
    }

    return $response;
  }
}

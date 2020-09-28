<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoginController extends AbstractController
{
    final public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $postBody = $request->getParsedBody();
        $response = $this->getResponse();
        // var_dump($_SESSION);
        if (isset($_SESSION['userid'])) {
          $user = $_SESSION['userid'];
          $response->getBody()->write("<h1>Already logged in as $user</h1>");
        } else if ($postBody['user'] == $_ENV['USER'] && $postBody['password'] == $_ENV['PASSWORD']) {
          $user = $postBody['user'];
          $response->getBody()->write("<h1>Welcome $user</h1>\n");
          $_SESSION['userid'] =  $user;
          echo("session started\n");
          var_dump($_SESSION);
        } else {
          // var_dump($postBody);
          echo("cookie:\n");
          var_dump($_COOKIE);
          echo("session:\n");
          var_dump($_SESSION);
          $response->getBody()->write("<h1>No (try posting user=alice&password=alice123)</h1>\n");
        }
        return $response;
    }
}

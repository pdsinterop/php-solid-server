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

        if ($request->getMethod() === 'POST') {
            if (isset($_SESSION['userid'])) {
              $user = $_SESSION['userid'];

              if (isset($request->getQueryParams()['returnUrl'])) {
                return $response
                    ->withHeader("Location", $request->getQueryParams()['returnUrl'])
                    ->withStatus(302)
                ;
              }

              $response->getBody()->write("<h1>Already logged in as $user</h1>");
            } elseif ($postBody['username'] && $postBody['password']) {
                $user = $postBody['username'];
                $password = $postBody['password'];

                if (
                    ($user === $_ENV['USERNAME'] && $password === $_ENV['PASSWORD'])
                    || ($user === $_SERVER['USERNAME'] && $password === $_SERVER['PASSWORD'])
                ) {
                    $_SESSION['userid'] =  $user;

                    if (isset($request->getQueryParams()['returnUrl'])) {
                        return $response
                            ->withHeader("Location", $request->getQueryParams()['returnUrl'])
                            ->withStatus(302)
                        ;
                    }

                    $response->getBody()->write("<h1>Welcome $user</h1>\n");
                } else {
                    $response->getBody()->write("<h1>Login as $user failed</h1>\n");
                }
            } else {
              $response->getBody()->write("<h1>Login failed</h1>\n");
            }
        } else {
            return $this->createTemplateResponse('login.html');
        }

        return $response;
    }
}

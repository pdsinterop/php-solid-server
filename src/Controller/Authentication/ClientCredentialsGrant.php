<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller\Authentication;

use Laminas\Diactoros\Stream;
use League\OAuth2\Server\Exception\OAuthServerException;
use Pdsinterop\Solid\Controller\AbstractController;
use Pdsinterop\Solid\Traits\HasAuthorizationServerTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ClientCredentialsGrant extends AbstractController
{
    use HasAuthorizationServerTrait;

    public function __invoke(ServerRequestInterface $request, array $args) : ResponseInterface
    {
        $response = $this->getResponse();
        $server = $this->getAuthorizationServer();

        try {
            // Try to respond to the request
            $response = $server->respondToAccessTokenRequest($request, $response);
        } catch (OAuthServerException $exception) {
            // All instances of OAuthServerException can be formatted into a HTTP response
            $response = $exception->generateHttpResponse($response);
        } catch (\Exception $exception) {
            // Unknown exception
//            $body = new Stream('php://temp', 'r+');
            $body = $response->getBody();
            $body->write($exception->getMessage());
            $response = $response->withStatus(500)->withBody($body);
        }

        return $response;
    }
}

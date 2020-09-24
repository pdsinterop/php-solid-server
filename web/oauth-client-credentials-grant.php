<?php declare(strict_types=1);

// https://oauth2.thephpleague.com/authorization-server/client-credentials-grant/

// Init our repositories
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use Pdsinterop\Solid\Authentication\AccessTokenRepository;
use Pdsinterop\Solid\Authentication\ClientRepository;
use Pdsinterop\Solid\Authentication\ScopeRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$clientRepository = new ClientRepository(); // instance of ClientRepositoryInterface
$scopeRepository = new ScopeRepository(); // instance of ScopeRepositoryInterface
$accessTokenRepository = new AccessTokenRepository(); // instance of AccessTokenRepositoryInterface

// Path to public and private keys
$privateKey = 'file://path/to/private.key';
//$privateKey = new CryptKey('file://path/to/private.key', 'passphrase'); // if private key has a pass phrase
$encryptionKey = 'lxZFUEsBCJ2Yb14IF2ygAHI5N4+ZAUXXaSeeJm6+twsUmIen'; // generate using base64_encode(random_bytes(32))

// Setup the authorization server
$server = new AuthorizationServer(
$clientRepository,
$accessTokenRepository,
$scopeRepository,
$privateKey,
$encryptionKey
);

// Enable the client credentials grant on the server
$server->enableGrantType(
new ClientCredentialsGrant(),
new \DateInterval('PT1H') // access tokens will expire after 1 hour
);

// ---------------------------------------------------------------------------------------------------------------------

$app->post('/access_token', function (ServerRequestInterface  $request, ResponseInterface $response) use ($app) {

  /* @var AuthorizationServer $server */
  $server = $app->getContainer()->get(AuthorizationServer::class);

  try {
      // Try to respond to the request
      return $server->respondToAccessTokenRequest($request, $response);
  } catch (OAuthServerException $exception) {
      // All instances of OAuthServerException can be formatted into a HTTP response
      return $exception->generateHttpResponse($response);
  } catch (\Exception $exception) {
      // Unknown exception
      $body = new \Laminas\Diactoros\Stream('php://temp', 'r+');
      $body->write($exception->getMessage());
      return $response->withStatus(500)->withBody($body);
  }
});
